const j=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:Function("return this")(),_=__DEFINES__;Object.keys(_).forEach(e=>{const t=e.split(".");let n=j;for(let r=0;r<t.length;r++){const o=t[r];r===t.length-1?n[o]=_[e]:n=n[o]||(n[o]={})}});function m(e){"@babel/helpers - typeof";return m=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(t){return typeof t}:function(t){return t&&typeof Symbol=="function"&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},m(e)}function z(e,t){if(m(e)!="object"||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(m(r)!="object")return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return(t==="string"?String:Number)(e)}function Q(e){var t=z(e,"string");return m(t)=="symbol"?t:t+""}function u(e,t,n){return(t=Q(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var B=class{constructor(e,t,n){this.logger=e,this.transport=t,this.importUpdatedModule=n,u(this,"hotModulesMap",new Map),u(this,"disposeMap",new Map),u(this,"pruneMap",new Map),u(this,"dataMap",new Map),u(this,"customListenersMap",new Map),u(this,"ctxToListenersMap",new Map),u(this,"currentFirstInvalidatedBy",void 0),u(this,"updateQueue",[]),u(this,"pendingUpdateQueue",!1)}async notifyListeners(e,t){const n=this.customListenersMap.get(e);n&&await Promise.allSettled(n.map(r=>r(t)))}send(e){this.transport.send(e).catch(t=>{this.logger.error(t)})}clear(){this.hotModulesMap.clear(),this.disposeMap.clear(),this.pruneMap.clear(),this.dataMap.clear(),this.customListenersMap.clear(),this.ctxToListenersMap.clear()}async prunePaths(e){await Promise.all(e.map(t=>{const n=this.disposeMap.get(t);if(n)return n(this.dataMap.get(t))})),await Promise.all(e.map(t=>{const n=this.pruneMap.get(t);if(n)return n(this.dataMap.get(t))}))}warnFailedUpdate(e,t){(!(e instanceof Error)||!e.message.includes("fetch"))&&this.logger.error(e),this.logger.error(`Failed to reload ${t}. This could be due to syntax errors or importing non-existent modules. (see errors above)`)}async queueUpdate(e){if(this.updateQueue.push(this.fetchUpdate(e)),!this.pendingUpdateQueue){this.pendingUpdateQueue=!0,await Promise.resolve(),this.pendingUpdateQueue=!1;const t=[...this.updateQueue];this.updateQueue=[],(await Promise.all(t)).forEach(n=>n&&n())}}async fetchUpdate(e){const{path:t,acceptedPath:n,firstInvalidatedBy:r}=e,o=this.hotModulesMap.get(t);if(!o)return;let i;const s=t===n,a=o.callbacks.filter(({deps:c})=>c.includes(n));if(s||a.length>0){const c=this.disposeMap.get(n);c&&await c(this.dataMap.get(n));try{i=await this.importUpdatedModule(e)}catch(l){this.warnFailedUpdate(l,n)}}return()=>{try{this.currentFirstInvalidatedBy=r;for(const{deps:l,fn:p}of a)p(l.map(h=>h===n?i:void 0));const c=s?t:`${n} via ${t}`;this.logger.debug(`hot updated: ${c}`)}finally{this.currentFirstInvalidatedBy=void 0}}}};let D="useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict",J=(e=21)=>{let t="",n=e|0;for(;n--;)t+=D[Math.random()*64|0];return t};typeof process<"u"&&process.platform;function V(){let e,t;return{promise:new Promise((n,r)=>{e=n,t=r}),resolve:e,reject:t}}function x(e){const t=new Error(e.message||"Unknown invoke error");return Object.assign(t,e,{runnerError:new Error("RunnerError")}),t}const G=e=>{if(e.invoke)return{...e,async invoke(n,r){const o=await e.invoke({type:"custom",event:"vite:invoke",data:{id:"send",name:n,data:r}});if("error"in o)throw x(o.error);return o.result}};if(!e.send||!e.connect)throw new Error("transport must implement send and connect when invoke is not implemented");const t=new Map;return{...e,connect({onMessage:n,onDisconnection:r}){return e.connect({onMessage(o){if(o.type==="custom"&&o.event==="vite:invoke"){const i=o.data;if(i.id.startsWith("response:")){const s=i.id.slice(9),a=t.get(s);if(!a)return;a.timeoutId&&clearTimeout(a.timeoutId),t.delete(s);const{error:c,result:l}=i.data;c?a.reject(c):a.resolve(l);return}}n(o)},onDisconnection:r})},disconnect(){return t.forEach(n=>{n.reject(new Error(`transport was disconnected, cannot call ${JSON.stringify(n.name)}`))}),t.clear(),e.disconnect?.()},send(n){return e.send(n)},async invoke(n,r){const o=J(),i={type:"custom",event:"vite:invoke",data:{name:n,id:`send:${o}`,data:r}},s=e.send(i),{promise:a,resolve:c,reject:l}=V(),p=e.timeout??6e4;let h;p>0&&(h=setTimeout(()=>{t.delete(o),l(new Error(`transport invoke timed out after ${p}ms (data: ${JSON.stringify(i)})`))},p),h?.unref?.()),t.set(o,{resolve:c,reject:l,name:n,timeoutId:h}),s&&s.catch(g=>{clearTimeout(h),t.delete(o),l(g)});try{return await a}catch(g){throw x(g)}}}},K=e=>{const t=G(e);let n=!t.connect,r;return{...e,...t.connect?{async connect(o){if(n)return;if(r){await r;return}const i=t.connect({onMessage:o??(()=>{}),onDisconnection(){n=!1}});i&&(r=i,await r,r=void 0),n=!0}}:{},...t.disconnect?{async disconnect(){n&&(r&&await r,n=!1,await t.disconnect())}}:{},async send(o){if(t.send){if(!n)if(r)await r;else throw new Error("send was called before connect");await t.send(o)}},async invoke(o,i){if(!n)if(r)await r;else throw new Error("invoke was called before connect");return t.invoke(o,i)}}},L=e=>{const t=e.pingInterval??3e4;let n,r;return{async connect({onMessage:o,onDisconnection:i}){const s=e.createConnection();s.addEventListener("message",async({data:c})=>{o(JSON.parse(c))});let a=s.readyState===s.OPEN;a||await new Promise((c,l)=>{s.addEventListener("open",()=>{a=!0,c()},{once:!0}),s.addEventListener("close",async()=>{if(!a){l(new Error("WebSocket closed without opened."));return}o({type:"custom",event:"vite:ws:disconnect",data:{webSocket:s}}),i()})}),o({type:"custom",event:"vite:ws:connect",data:{webSocket:s}}),n=s,r=setInterval(()=>{s.readyState===s.OPEN&&s.send(JSON.stringify({type:"ping"}))},t)},disconnect(){clearInterval(r),n?.close()},send(o){n.send(JSON.stringify(o))}}};function Y(e){const t=new Z;return n=>t.enqueue(()=>e(n))}var Z=class{constructor(){u(this,"queue",[]),u(this,"pending",!1)}enqueue(e){return new Promise((t,n)=>{this.queue.push({promise:e,resolve:t,reject:n}),this.dequeue()})}dequeue(){if(this.pending)return!1;const e=this.queue.shift();return e?(this.pending=!0,e.promise().then(e.resolve).catch(e.reject).finally(()=>{this.pending=!1,this.dequeue()}),!0):!1}};const X=__HMR_CONFIG_NAME__,ee=__BASE__||"/",te="document"in globalThis?document.querySelector("meta[property=csp-nonce]")?.nonce:void 0;function d(e,t={},...n){const r=document.createElement(e);for(const[o,i]of Object.entries(t))i!==void 0&&r.setAttribute(o,i);return r.append(...n),r}const ne=`
:host {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 99999;
  --monospace: 'SFMono-Regular', Consolas,
  'Liberation Mono', Menlo, Courier, monospace;
  --red: #ff5555;
  --yellow: #e2aa53;
  --purple: #cfa4ff;
  --cyan: #2dd9da;
  --dim: #c9c9c9;

  --window-background: #181818;
  --window-color: #d8d8d8;
}

.backdrop {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow-y: scroll;
  margin: 0;
  background: rgba(0, 0, 0, 0.66);
}

.window {
  font-family: var(--monospace);
  line-height: 1.5;
  max-width: 80vw;
  color: var(--window-color);
  box-sizing: border-box;
  margin: 30px auto;
  padding: 2.5vh 4vw;
  position: relative;
  background: var(--window-background);
  border-radius: 6px 6px 8px 8px;
  box-shadow: 0 19px 38px rgba(0,0,0,0.30), 0 15px 12px rgba(0,0,0,0.22);
  overflow: hidden;
  border-top: 8px solid var(--red);
  direction: ltr;
  text-align: left;
}

pre {
  font-family: var(--monospace);
  font-size: 16px;
  margin-top: 0;
  margin-bottom: 1em;
  overflow-x: scroll;
  scrollbar-width: none;
}

pre::-webkit-scrollbar {
  display: none;
}

pre.frame::-webkit-scrollbar {
  display: block;
  height: 5px;
}

pre.frame::-webkit-scrollbar-thumb {
  background: #999;
  border-radius: 5px;
}

pre.frame {
  scrollbar-width: thin;
}

.message {
  line-height: 1.3;
  font-weight: 600;
  white-space: pre-wrap;
}

.message-body {
  color: var(--red);
}

.plugin {
  color: var(--purple);
}

.file {
  color: var(--cyan);
  margin-bottom: 0;
  white-space: pre-wrap;
  word-break: break-all;
}

.frame {
  color: var(--yellow);
}

.stack {
  font-size: 13px;
  color: var(--dim);
}

.tip {
  font-size: 13px;
  color: #999;
  border-top: 1px dotted #999;
  padding-top: 13px;
  line-height: 1.8;
}

code {
  font-size: 13px;
  font-family: var(--monospace);
  color: var(--yellow);
}

.file-link {
  text-decoration: underline;
  cursor: pointer;
}

kbd {
  line-height: 1.5;
  font-family: ui-monospace, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size: 0.75rem;
  font-weight: 700;
  background-color: rgb(38, 40, 44);
  color: rgb(166, 167, 171);
  padding: 0.15rem 0.3rem;
  border-radius: 0.25rem;
  border-width: 0.0625rem 0.0625rem 0.1875rem;
  border-style: solid;
  border-color: rgb(54, 57, 64);
  border-image: initial;
}
`,oe=()=>d("div",{class:"backdrop",part:"backdrop"},d("div",{class:"window",part:"window"},d("pre",{class:"message",part:"message"},d("span",{class:"plugin",part:"plugin"}),d("span",{class:"message-body",part:"message-body"})),d("pre",{class:"file",part:"file"}),d("pre",{class:"frame",part:"frame"}),d("pre",{class:"stack",part:"stack"}),d("div",{class:"tip",part:"tip"},"Click outside, press ",d("kbd",{},"Esc")," key, or fix the code to dismiss.",d("br"),"You can also disable this overlay by setting ",d("code",{part:"config-option-name"},"server.hmr.overlay")," to ",d("code",{part:"config-option-value"},"false")," in ",d("code",{part:"config-file-name"},X),".")),d("style",{nonce:te},ne)),P=/(?:file:\/\/)?(?:[a-zA-Z]:\\|\/).*?:\d+:\d+/g,b=/^(?:>?\s*\d+\s+\|.*|\s+\|\s*\^.*)\r?\n/gm,{HTMLElement:re=class{}}=globalThis;var ie=class extends re{constructor(e,t=!0){super(),u(this,"root",void 0),u(this,"closeOnEsc",void 0),this.root=this.attachShadow({mode:"open"}),this.root.appendChild(oe()),b.lastIndex=0;const n=e.frame&&b.test(e.frame),r=n?e.message.replace(b,""):e.message;e.plugin&&this.text(".plugin",`[plugin:${e.plugin}] `),this.text(".message-body",r.trim());const[o]=(e.loc?.file||e.id||"unknown file").split("?");e.loc?this.text(".file",`${o}:${e.loc.line}:${e.loc.column}`,t):e.id&&this.text(".file",o),n&&this.text(".frame",e.frame.trim()),this.text(".stack",e.stack,t),this.root.querySelector(".window").addEventListener("click",i=>{i.stopPropagation()}),this.addEventListener("click",()=>{this.close()}),this.closeOnEsc=i=>{(i.key==="Escape"||i.code==="Escape")&&this.close()},document.addEventListener("keydown",this.closeOnEsc)}text(e,t,n=!1){const r=this.root.querySelector(e);if(!n)r.textContent=t;else{let o=0,i;for(P.lastIndex=0;i=P.exec(t);){const{0:s,index:a}=i,c=t.slice(o,a);r.appendChild(document.createTextNode(c));const l=document.createElement("a");l.textContent=s,l.className="file-link",l.onclick=()=>{fetch(new URL(`${ee}__open-in-editor?file=${encodeURIComponent(s)}`,import.meta.url))},r.appendChild(l),o+=c.length+s.length}o<t.length&&r.appendChild(document.createTextNode(t.slice(o)))}}close(){this.parentNode?.removeChild(this),document.removeEventListener("keydown",this.closeOnEsc)}};const v="vite-error-overlay",{customElements:y}=globalThis;y&&!y.get(v)&&y.define(v,ie);console.debug("[vite] connecting...");const k=new URL(import.meta.url),se=__SERVER_HOST__,T=__HMR_PROTOCOL__||(k.protocol==="https:"?"wss":"ws"),H=__HMR_PORT__,R=`${__HMR_HOSTNAME__||k.hostname}:${H||k.port}${__HMR_BASE__}`,O=__HMR_DIRECT_TARGET__,E=__BASE__||"/",U=__HMR_TIMEOUT__,C=__WS_TOKEN__,A=K((()=>{let e=L({createConnection:()=>new WebSocket(`${T}://${R}?token=${C}`,"vite-hmr"),pingInterval:U});return{async connect(t){try{await e.connect(t)}catch(n){if(!H){e=L({createConnection:()=>new WebSocket(`${T}://${O}?token=${C}`,"vite-hmr"),pingInterval:U});try{await e.connect(t),console.info("[vite] Direct websocket connection fallback. Check out https://vite.dev/config/server-options.html#server-hmr to remove the previous connection error.")}catch(r){if(r instanceof Error&&r.message.includes("WebSocket closed without opened.")){const o=new URL(import.meta.url),i=o.host+o.pathname.replace(/@vite\/client$/,"");console.error(`[vite] failed to connect to websocket.
your current setup:
  (browser) ${i} <--[HTTP]--> ${se} (server)
  (browser) ${R} <--[WebSocket (failing)]--> ${O} (server)
Check out your Vite / network configuration and https://vite.dev/config/server-options.html#server-hmr .`)}}return}throw console.error(`[vite] failed to connect to websocket (${n}). `),n}},async disconnect(){await e.disconnect()},send(t){e.send(t)}}})());let W=!1;typeof window<"u"&&window.addEventListener?.("beforeunload",()=>{W=!0});function I(e){const t=new URL(e,"http://vite.dev");return t.searchParams.delete("direct"),t.pathname+t.search}let $=!0;const N=new WeakSet,ae=e=>{let t;return()=>{t&&(clearTimeout(t),t=null),t=setTimeout(()=>{location.reload()},e)}},M=ae(20),f=new B({error:e=>console.error("[vite]",e),debug:(...e)=>console.debug("[vite]",...e)},A,async function({acceptedPath:t,timestamp:n,explicitImportRequired:r,isWithinCircularImport:o}){const[i,s]=t.split("?"),a=import(E+i.slice(1)+`?${r?"import&":""}t=${n}${s?`&${s}`:""}`);return o&&a.catch(()=>{console.info(`[hmr] ${t} failed to apply HMR as it's within a circular import. Reloading page to reset the execution order. To debug and break the circular import, you can run \`vite --debug hmr\` to log the circular dependency path if a file change triggered it.`),M()}),await a});A.connect(Y(ce));async function ce(e){switch(e.type){case"connected":console.debug("[vite] connected.");break;case"update":if(await f.notifyListeners("vite:beforeUpdate",e),w)if($&&de()){location.reload();return}else q&&F(),$=!1;await Promise.all(e.updates.map(async t=>{if(t.type==="js-update")return f.queueUpdate(t);const{path:n,timestamp:r}=t,o=I(n),i=Array.from(document.querySelectorAll("link")).find(a=>!N.has(a)&&I(a.href).includes(o));if(!i)return;const s=`${E}${o.slice(1)}${o.includes("?")?"&":"?"}t=${r}`;return new Promise(a=>{const c=i.cloneNode();c.href=new URL(s,i.href).href;const l=()=>{i.remove(),console.debug(`[vite] css hot updated: ${o}`),a()};c.addEventListener("load",l),c.addEventListener("error",l),N.add(i),i.after(c)})})),await f.notifyListeners("vite:afterUpdate",e);break;case"custom":if(await f.notifyListeners(e.event,e.data),e.event==="vite:ws:disconnect"&&w&&!W){console.log("[vite] server connection lost. Polling for restart...");const t=e.data.webSocket,n=new URL(t.url);n.search="",await ue(n.href),location.reload()}break;case"full-reload":if(await f.notifyListeners("vite:beforeFullReload",e),w)if(e.path&&e.path.endsWith(".html")){const t=decodeURI(location.pathname),n=E+e.path.slice(1);(t===n||e.path==="/index.html"||t.endsWith("/")&&t+"index.html"===n)&&M();return}else M();break;case"prune":await f.notifyListeners("vite:beforePrune",e),await f.prunePaths(e.paths);break;case"error":if(await f.notifyListeners("vite:error",e),w){const t=e.err;q?le(t):console.error(`[vite] Internal Server Error
${t.message}
${t.stack}`)}break;case"ping":break;default:return e}}const q=__HMR_ENABLE_OVERLAY__,w="document"in globalThis;function le(e){F();const{customElements:t}=globalThis;if(t){const n=t.get(v);document.body.appendChild(new n(e))}}function F(){document.querySelectorAll(v).forEach(e=>e.close())}function de(){return document.querySelectorAll(v).length}function ue(e){if(typeof SharedWorker>"u"){const o={currentState:document.visibilityState,listeners:new Set},i=()=>{o.currentState=document.visibilityState;for(const s of o.listeners)s(o.currentState)};return document.addEventListener("visibilitychange",i),S(e,o)}const t=new Blob(['"use strict";',`const waitForSuccessfulPingInternal = ${S.toString()};`,`const fn = ${pe.toString()};`,`fn(${JSON.stringify(e)})`],{type:"application/javascript"}),n=URL.createObjectURL(t),r=new SharedWorker(n);return new Promise((o,i)=>{const s=()=>{r.port.postMessage({visibility:document.visibilityState})};document.addEventListener("visibilitychange",s),r.port.addEventListener("message",a=>{document.removeEventListener("visibilitychange",s),r.port.close();const c=a.data;if(c.type==="error"){i(c.error);return}o()}),s(),r.port.start()})}function pe(e){self.addEventListener("connect",t=>{const n=t.ports[0];if(!e){n.postMessage({type:"error",error:new Error("socketUrl not found")});return}const r={currentState:"visible",listeners:new Set};n.addEventListener("message",o=>{const{visibility:i}=o.data;r.currentState=i,console.debug("[vite] new window visibility",i);for(const s of r.listeners)s(i)}),n.start(),console.debug("[vite] connected from window"),S(e,r).then(()=>{console.debug("[vite] ping successful");try{n.postMessage({type:"success"})}catch(o){n.postMessage({type:"error",error:o})}},o=>{console.debug("[vite] error happened",o);try{n.postMessage({type:"error",error:o})}catch(i){n.postMessage({type:"error",error:i})}})})}async function S(e,t,n=1e3){function r(s){return new Promise(a=>setTimeout(a,s))}async function o(){try{const s=new WebSocket(e,"vite-ping");return new Promise(a=>{function c(){a(!0),p()}function l(){a(!1),p()}function p(){s.removeEventListener("open",c),s.removeEventListener("error",l),s.close()}s.addEventListener("open",c),s.addEventListener("error",l)})}catch{return!1}}function i(s){return new Promise(a=>{const c=l=>{l==="visible"&&(a(),s.listeners.delete(c))};s.listeners.add(c)})}if(!await o())for(await r(n);;)if(t.currentState==="visible"){if(await o())break;await r(n)}else await i(t)}const fe=new Map,he=new Map;"document"in globalThis&&(document.querySelectorAll("style[data-vite-dev-id]").forEach(e=>{fe.set(e.getAttribute("data-vite-dev-id"),e)}),document.querySelectorAll('link[rel="stylesheet"][data-vite-dev-id]').forEach(e=>{he.set(e.getAttribute("data-vite-dev-id"),e)}));
