import{x as O,V as A}from"./index-D2rZqg1R.js";/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const l={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=(...e)=>{const t=new Set;for(const n of e)if(typeof n=="string")for(const o of n.split(/\s+/))o&&t.add(o);return[...t].join(" ")};/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function y(e){return e!=null}function T(e,t={}){var k,a;const n=t.attributeNames??{},o=i=>n[i]??i,r=e.size??e.width??l.width,u=e.size??e.height??l.height,h=((k=e.aliases)==null?void 0:k.filter(i=>typeof i=="string"&&i.trim()!=="").map(i=>`lucide-${i}`))??[],f=[...e.name?[`lucide-${e.name}`]:[],...h],w=((a=t.className)==null?void 0:a.split(" ").filter(Boolean))??[],g=t.includeDefaultClasses===!1?C(...w):C("lucide",...f,...w),d=t.absoluteStrokeWidth?Number(t.strokeWidth??l["stroke-width"])*Number(e.size??e.width??l.width)/Number(t.size??t.width??l.width):t.strokeWidth??l["stroke-width"];return["svg",{...Object.entries(l).reduce((i,[s,c])=>(i[o(s)]=c,i),{}),..."color"in t&&t.color&&{[o("stroke")]:t.color},..."size"in t&&y(t.size)&&{[o("width")]:t.size,[o("height")]:t.size},..."width"in t&&y(t.width)&&{[o("width")]:t.width},..."height"in t&&y(t.height)&&{[o("height")]:t.height},[o("stroke-width")]:d,...g&&{[o("class")]:g},[o("viewBox")]:`0 0 ${r} ${u}`,...t.hasA11yProp===!1?{[o("aria-hidden")]:"true"}:{},..."attributes"in t&&t.attributes},e.node.map(i=>{const[s,c,b]=i,S=t.nonScalingStroke?{[o("vector-effect")]:"non-scaling-stroke",...c}:c;return b?[s,S,b]:[s,S]})]}/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const X=e=>{if(e==null||typeof e!="object")return!1;for(const t in e)if(Object.prototype.hasOwnProperty.call(e,t)&&(t.startsWith("aria-")||t==="role"||t==="title")&&e[t]!=null)return!0;return!1};/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=e=>e==="";/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Z=e=>(e.match(/\d+[xX]\d+|[A-Z]?[a-z]+|[A-Z]|\d+/g)??[]).join("-").toLowerCase();/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const D=Symbol("lucide-icons");function F(){return O(D,{})}/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const H=({name:e,iconNode:t,"icon-node":n,icon:o={name:e&&Z(e),node:t??n??[],size:24,aliases:[]},absoluteStrokeWidth:r,"absolute-stroke-width":u,nonScalingStroke:h,"non-scaling-stroke":f,strokeWidth:w,"stroke-width":g,size:d,width:W=d,height:k=d,color:a,...i},{slots:s})=>{var v;const{size:c,color:b,strokeWidth:S=2,absoluteStrokeWidth:z=!1,nonScalingStroke:j=!1,class:B=""}=F(),I=x(r)||x(u)||r===!0||u===!0||z===!0,L=x(h)||x(f)||h===!0||f===!0||j===!0;delete i.class;const N=(v=s.default)==null?void 0:v.call(s),[,P,$=[]]=T(o,{color:a??b,width:W??d??c,height:k??d??c,strokeWidth:w??g??S,absoluteStrokeWidth:I,nonScalingStroke:L,className:B,hasA11yProp:N!=null&&N.length>0||X(i),attributes:i});return A("svg",P,[...$.map(([E,m])=>A(E,m)),...N??[]])};/**
 * @license @lucide/vue v1.77.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function V(e,t=[]){const n=typeof e=="string"?{name:e,node:t}:e;return(o,{slots:r})=>A(H,{...o,icon:n},r.default?{default:r.default}:void 0)}export{V as c};
