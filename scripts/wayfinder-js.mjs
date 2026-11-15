import { parse as babelParse } from '@babel/parser';
import { readFileSync, writeFileSync, unlinkSync } from 'node:fs';
import { readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';

const TYPE_DECLS = new Set([
    'TSInterfaceDeclaration',
    'TSTypeAliasDeclaration',
    'TSDeclareFunction',
    'TSEnumDeclaration',
]);
const SKIP = new Set([
    'loc',
    'start',
    'end',
    'range',
    'leadingComments',
    'trailingComments',
    'innerComments',
    'comments',
    'tokens',
    'errors',
    'extra',
]);

function walkAll(node, fn) {
    if (!node || typeof node !== 'object') return;
    if (Array.isArray(node)) {
        node.forEach((c) => walkAll(c, fn));
        return;
    }
    if (typeof node.type !== 'string') return;
    fn(node);
    for (const key of Object.keys(node)) {
        if (SKIP.has(key)) continue;
        const value = node[key];
        if (value && typeof value === 'object') walkAll(value, fn);
    }
}

function collectRanges(code) {
    const ast = babelParse(code, {
        sourceType: 'module',
        plugins: ['typescript'],
        errorRecovery: true,
    });
    const ranges = [];

    walkAll(ast.program, (node) => {
        if (TYPE_DECLS.has(node.type)) {
            ranges.push([node.start, node.end]);
            return;
        }

        if (node.type === 'ImportDeclaration') {
            const specs = node.specifiers ?? [];
            const named = specs.filter((sp) => sp.importKind !== 'type');
            if (
                node.importKind === 'type' ||
                (specs.length > 0 && named.length === 0)
            ) {
                ranges.push([node.start, node.end]);
                return;
            }
            specs.forEach((sp, i) => {
                if (sp.importKind !== 'type') return;
                const next = specs[i + 1];
                let end = sp.end;
                if (next) {
                    end = next.start;
                } else if (code[end] === ',') {
                    end += 1;
                }
                ranges.push([sp.start, end]);
            });
        }

        if (
            node.type === 'ExportNamedDeclaration' &&
            node.exportKind === 'type'
        ) {
            ranges.push([node.start, node.end]);
            return;
        }

        for (const key of [
            'typeAnnotation',
            'returnType',
            'typeParameters',
            'typeArguments',
            'superTypeParameters',
        ]) {
            const n = node[key];
            if (n && typeof n.start === 'number') {
                let from = n.start;
                const to =
                    typeof n.typeAnnotation?.end === 'number'
                        ? n.typeAnnotation.end
                        : n.end;
                if (from > 0 && code[from - 1] === '?') from -= 1;
                ranges.push([from, to]);
            }
        }

        if (
            node.type === 'TSAsExpression' ||
            node.type === 'TSSatisfiesExpression'
        ) {
            const kw = node.type === 'TSAsExpression' ? ' as ' : ' satisfies ';
            const idx = code.indexOf(kw, node.expression.end);
            ranges.push([
                idx !== -1 && idx < node.end ? idx : node.expression.end,
                node.end,
            ]);
        }

        if (node.type === 'TSNonNullExpression')
            ranges.push([node.expression.end, node.end]);

        if (
            node.optional === true &&
            ['Identifier', 'ObjectPattern', 'ArrayPattern'].includes(
                node.type,
            ) &&
            typeof node.start === 'number'
        ) {
            const q =
                node.typeAnnotation &&
                typeof node.typeAnnotation.start === 'number'
                    ? node.typeAnnotation.start - 1
                    : node.start;
            if (q >= 0 && code[q] === '?') ranges.push([q, q + 1]);
        }
    });

    return ranges;
}

function applyRanges(text, ranges) {
    const items = ranges
        .filter(
            ([a, b]) => typeof a === 'number' && typeof b === 'number' && b > a,
        )
        .sort((x, y) => x[0] - y[0]);

    const merged = [];
    for (const [start, end] of items) {
        const last = merged[merged.length - 1];
        if (last && start <= last[1]) last[1] = Math.max(last[1], end);
        else merged.push([start, end]);
    }

    let out = '';
    let cursor = 0;
    for (const [start, end] of merged) {
        out += text.slice(cursor, start);
        cursor = end;
    }
    return out + text.slice(cursor);
}

function tidy(code) {
    return code
        .replace(/\(\s*([^()\n]+?)\s*,\s*(?=\n\s*\})/g, '($1)')
        .replace(/\([^()]*?\s+as\s+[^()]*?\)/g, (m) =>
            m.slice(1, -1).replace(/\s+as\s+.*/, ''),
        )
        .replace(/\bas\s+[A-Za-z_$][\w$.<>[\]| ]*(?=[,);\n])/g, '')
        .replace(/^(import[^\n]*?)\s*,\s*\}/gm, '$1 }')
        .replace(/^(import\s*\{)\s*,/gm, '$1')
        .replace(/,\s*,/g, ',')
        .replace(/\(\s*,/g, '(')
        .replace(/,\s*\)/g, ')')
        .replace(/([A-Za-z_$][\w$]*)\?(\s*[,)])/g, '$1$2')
        .replace(/\n[ \t]*\n[ \t]*\n+/g, '\n\n');
}

function files(dir) {
    let out = [];
    for (const name of readdirSync(dir)) {
        const p = join(dir, name);
        const st = statSync(p);
        if (st.isDirectory()) out = out.concat(files(p));
        else if (extname(p) === '.ts' && !p.endsWith('.d.ts')) out.push(p);
    }
    return out;
}

const targets = [
    'resources/js/actions',
    'resources/js/routes',
    'resources/js/wayfinder',
].flatMap((d) => {
    try {
        return files(d);
    } catch {
        return [];
    }
});

let converted = 0;
for (const file of targets) {
    const src = readFileSync(file, 'utf8');
    const out = tidy(applyRanges(src, collectRanges(src)));
    if (!out.trim()) {
        unlinkSync(file);
        continue;
    }
    writeFileSync(file.replace(/\.ts$/, '.js'), out);
    unlinkSync(file);
    converted++;
}

console.log(`wayfinder-js: converted ${converted} file(s)`);
