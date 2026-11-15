import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { parse as parseSfc } from '@vue/compiler-sfc';

const BUILTINS = new Set([
    'true',
    'false',
    'null',
    'undefined',
    'this',
    'new',
    'typeof',
    'in',
    'of',
    'Math',
    'Date',
    'JSON',
    'Object',
    'Array',
    'String',
    'Number',
    'Boolean',
    'window',
    'document',
    'console',
    'slot',
    '$props',
    '$slots',
    '$attrs',
    '$emit',
]);

function walk(dir) {
    let files = [];
    for (const name of readdirSync(dir)) {
        const p = join(dir, name);
        if (statSync(p).isDirectory()) files = files.concat(walk(p));
        else if (p.endsWith('.vue')) files.push(p);
    }
    return files;
}

function declaredNames(script) {
    const names = new Set();

    for (const m of script.matchAll(
        /\b(?:const|let|var)\s+([A-Za-z_$][\w$]*)/g,
    ))
        names.add(m[1]);
    for (const m of script.matchAll(/\bfunction\s+([A-Za-z_$][\w$]*)/g))
        names.add(m[1]);
    for (const m of script.matchAll(
        /\bimport\s+(?:([\w$]+)\s*,?\s*)?(?:\{([^}]*)\})?/g,
    )) {
        if (m[1]) names.add(m[1]);
        if (m[2])
            for (const part of m[2].split(',')) {
                const cleaned = part
                    .trim()
                    .split(/\s+as\s+/)
                    .pop();
                if (cleaned && /^[A-Za-z_$][\w$]*$/.test(cleaned))
                    names.add(cleaned);
            }
    }
    // destructured const { a, b } = ...
    for (const m of script.matchAll(/\b(?:const|let|var)\s*\{([^}]*)\}/g)) {
        for (const part of m[1].split(',')) {
            const name = part.trim().split(':').pop().trim();
            if (/^[A-Za-z_$][\w$]*$/.test(name)) names.add(name);
        }
    }
    // defineProps keys, parsed with balanced braces so nested options work.
    const marker = 'defineProps(';
    let index = script.indexOf(marker);

    while (index !== -1) {
        const open = script.indexOf('{', index);

        if (open !== -1) {
            let depth = 0;
            let end = -1;

            for (let i = open; i < script.length; i++) {
                if (script[i] === '{') depth++;
                else if (script[i] === '}') {
                    depth--;
                    if (depth === 0) {
                        end = i;
                        break;
                    }
                }
            }

            if (end !== -1) {
                for (const m of script
                    .slice(open + 1, end)
                    .matchAll(/(?:^|,)\s*([A-Za-z_$][\w$]*)\s*:/g)) {
                    names.add(m[1]);
                }
            }
        }

        index = script.indexOf(marker, index + marker.length);
    }

    return names;
}

function templateVars(template) {
    const names = new Set();

    const addParts = (raw) => {
        for (const part of raw.split(',')) {
            const name = part
                .trim()
                .replace(/^\{/, '')
                .replace(/\}$/, '')
                .split(':')
                .pop()
                .split('=')[0]
                .trim();
            if (/^[A-Za-z_$][\w$]*$/.test(name)) names.add(name);
        }
    };

    for (const m of template.matchAll(/v-for="([^"]*)"/g)) {
        const inMatch = m[1].match(
            /^\s*(\{[^}]*\}|\([^)]*\)|[A-Za-z_$][\w$]*)\s+in\s/,
        );
        if (inMatch) addParts(inMatch[1].replace(/^[({]|[)}]$/g, ''));
    }

    for (const m of template.matchAll(
        /(?:v-slot(?::[\w-]+)?|#[\w-]+)="([^"]*)"/g,
    )) {
        addParts(m[1].replace(/^[({]|[)}]$/g, ''));
    }

    return names;
}

let problems = 0;

for (const file of walk('resources/js')) {
    const { descriptor } = parseSfc(readFileSync(file, 'utf8'), {
        filename: file,
    });
    if (!descriptor.scriptSetup || !descriptor.template) continue;

    const known = new Set([
        ...declaredNames(descriptor.scriptSetup.content),
        ...templateVars(descriptor.template.content),
    ]);

    const template = descriptor.template.content;

    for (const m of template.matchAll(
        /:([a-zA-Z][\w-]*)(?:\.\w+)*="([A-Za-z_$][\w$]*)"/g,
    )) {
        const identifier = m[2];

        if (BUILTINS.has(identifier) || known.has(identifier)) continue;

        console.log(
            `${file}: :${m[1]}="${identifier}" -> "${identifier}" is not declared`,
        );
        problems++;
    }
}

if (problems === 0) {
    console.log('OK: no undeclared template bindings');
    process.exit(0);
}

console.error(`FOUND ${problems} undeclared binding(s)`);
process.exit(1);
