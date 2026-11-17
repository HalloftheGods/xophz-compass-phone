import{i as m,P as v}from"./index-KpXgXy0g.js";/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const l={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const z=(...e)=>e.filter((t,s,o)=>!!t&&t.trim()!==""&&o.indexOf(t)===s).join(" ").trim();/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function W(e){return e!=null}function D(e,t={}){var g,b;const s=t.attributeNames??{},o=i=>s[i]??i,r=e.size??e.width??l.width,u=e.size??e.height??l.height,h=((g=e.aliases)==null?void 0:g.filter(i=>typeof i=="string"&&i.trim()!=="").map(i=>`lucide-${i}`))??[],f=[...e.name?[`lucide-${e.name}`]:[],...h],w=((b=t.className)==null?void 0:b.split(" ").filter(Boolean))??[],k=t.includeDefaultClasses===!1?z(...w):z("lucide",...f,...w),d=t.absoluteStrokeWidth?Number(t.strokeWidth??l["stroke-width"])*Number(e.size??e.width??l.width)/Number(t.size??t.width??l.width):t.strokeWidth??l["stroke-width"];return["svg",{...Object.entries(l).reduce((i,[n,c])=>(i[o(n)]=c,i),{}),..."color"in t&&t.color&&{[o("stroke")]:t.color},..."size"in t&&W(t.size)&&{[o("width")]:t.size,[o("height")]:t.size},..."width"in t&&W(t.width)&&{[o("width")]:t.width},..."height"in t&&W(t.height)&&{[o("height")]:t.height},[o("stroke-width")]:d,...k&&{[o("class")]:k},[o("viewBox")]:`0 0 ${r} ${u}`,...t.hasA11yProp===!1?{[o("aria-hidden")]:"true"}:{},..."attributes"in t&&t.attributes},e.node.map(i=>{const[n,c,a]=i,S=t.nonScalingStroke?{[o("vector-effect")]:"non-scaling-stroke",...c}:c;return a?[n,S,a]:[n,S]})]}/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const F=e=>{for(const t in e)if(t.startsWith("aria-")||t==="role"||t==="title")return!0;return!1};/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=e=>e==="";/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const H=e=>e==null?void 0:e.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const O=Symbol("lucide-icons");function U(){return m(O,{})}/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const X=({name:e,iconNode:t,"icon-node":s,icon:o={name:e&&H(e),node:t??s??[],size:24,aliases:[]},absoluteStrokeWidth:r,"absolute-stroke-width":u,nonScalingStroke:h,"non-scaling-stroke":f,strokeWidth:w,"stroke-width":k,size:d,width:A=d,height:g=d,color:b,...i},{slots:n})=>{var C;const{size:c,color:a,strokeWidth:S=2,absoluteStrokeWidth:y=!1,nonScalingStroke:B=!1,class:$=""}=U(),I=x(r)||x(u)||r===!0||u===!0||y===!0,L=x(h)||x(f)||h===!0||f===!0||B===!0;delete i.class;const N=(C=n.default)==null?void 0:C.call(n),[,P,j=[]]=D(o,{color:b??a,width:A??d??c,height:g??d??c,strokeWidth:w??k??S,absoluteStrokeWidth:I,nonScalingStroke:L,className:$,hasA11yProp:N!=null&&N.length>0||F(i),attributes:i});return v("svg",P,[...j.map(([E,T])=>v(E,T)),...N??[]])};/**
 * @license @lucide/vue v1.73.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */function _(e,t=[]){const s=typeof e=="string"?{name:e,node:t}:e;return(o,{slots:r})=>v(X,{...o,icon:s},r.default?{default:r.default}:void 0)}export{_ as c};
