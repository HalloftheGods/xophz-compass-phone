import{x as T,V as W}from"./index-CIkcLKgC.js";/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const l={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=(...e)=>{const t=new Set;for(const n of e)if(typeof n=="string")for(const o of n.split(/\s+/))o&&t.add(o);return[...t].join(" ")};/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function y(e){return e!=null}function m(e,t={}){var a,g;const n=t.attributeNames??{},o=i=>n[i]??i,c=e.size??e.width??l.width,d=e.size??e.height??l.height,h=((a=e.aliases)==null?void 0:a.filter(i=>typeof i=="string"&&i.trim()!=="").map(i=>`lucide-${i}`))??[],f=[...e.name?[`lucide-${e.name}`]:[],...h],w=((g=t.className)==null?void 0:g.split(" ").filter(Boolean))??[],k=t.includeDefaultClasses===!1?C(...w):C("lucide",...f,...w),u=t.absoluteStrokeWidth?Number(t.strokeWidth??l["stroke-width"])*Number(e.size??e.width??l.width)/Number(t.size??t.width??l.width):t.strokeWidth??l["stroke-width"];return["svg",{...Object.entries(l).reduce((i,[s,r])=>(i[o(s)]=r,i),{}),..."color"in t&&t.color&&{[o("stroke")]:t.color},..."size"in t&&y(t.size)&&{[o("width")]:t.size,[o("height")]:t.size},..."width"in t&&y(t.width)&&{[o("width")]:t.width},..."height"in t&&y(t.height)&&{[o("height")]:t.height},[o("stroke-width")]:u,...k&&{[o("class")]:k},[o("viewBox")]:`0 0 ${c} ${d}`,...t.hasA11yProp===!1?{[o("aria-hidden")]:"true"}:{},..."attributes"in t&&t.attributes},e.node.map(i=>{const[s,r,b]=i,S=t.nonScalingStroke?{[o("vector-effect")]:"non-scaling-stroke",...r}:r;return b?[s,S,b]:[s,S]})]}/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const D=e=>{if(e==null||typeof e!="object")return!1;for(const t in e)if(Object.prototype.hasOwnProperty.call(e,t)&&(t.startsWith("aria-")||t==="role"||t==="title")&&e[t]!=null)return!0;return!1};/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const N=e=>e==="";/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const F=e=>e==null?void 0:e.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const H=Symbol("lucide-icons");function U(){return T(H,{})}/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const V=({name:e,iconNode:t,"icon-node":n,icon:o={name:e&&F(e),node:t??n??[],size:24,aliases:[]},absoluteStrokeWidth:c,"absolute-stroke-width":d,nonScalingStroke:h,"non-scaling-stroke":f,strokeWidth:w,"stroke-width":k,size:u,width:v=u,height:a=u,color:g,...i},{slots:s})=>{var A;const{size:r,color:b,strokeWidth:S=2,absoluteStrokeWidth:z=!1,nonScalingStroke:j=!1,class:$=""}=U(),B=N(c)||N(d)||c===!0||d===!0||z===!0,I=N(h)||N(f)||h===!0||f===!0||j===!0;delete i.class;const x=(A=s.default)==null?void 0:A.call(s),[,L,P=[]]=m(o,{color:g??b,width:v??u??r,height:a??u??r,strokeWidth:w??k??S,absoluteStrokeWidth:B,nonScalingStroke:I,className:$,hasA11yProp:x!=null&&x.length>0||D(i),attributes:i});return W("svg",L,[...P.map(([E,O])=>W(E,O)),...x??[]])};/**
 * @license @lucide/vue v1.75.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function Z(e,t=[]){const n=typeof e=="string"?{name:e,node:t}:e;return(o,{slots:c})=>W(V,{...o,icon:n},c.default?{default:c.default}:void 0)}export{Z as c};
