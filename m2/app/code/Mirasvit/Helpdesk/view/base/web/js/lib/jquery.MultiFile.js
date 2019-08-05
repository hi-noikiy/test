/* @codingStandardsIgnoreFile jquery-multifile v2.1.0 @ 2014-07-02 10:07:41 */window.jQuery&&function(a){"use strict";function b(a){return a>1048576?(a/1048576).toFixed(1)+"Mb":1024==a?"1Mb":(a/1024).toFixed(1)+"Kb"}a.fn.MultiFile=function(c){if(0==this.length)return this;if("string"==typeof arguments[0]){if(this.length>1){var d=arguments;return this.each(function(){a.fn.MultiFile.apply(a(this),d)})}return a.fn.MultiFile[arguments[0]].apply(this,a.makeArray(arguments).slice(1)||[])}"number"==typeof c&&(c={max:c});var c=a.extend({},a.fn.MultiFile.options,c||{});a("form").not("MultiFile-intercepted").addClass("MultiFile-intercepted").submit(a.fn.MultiFile.disableEmpty),a.fn.MultiFile.options.autoIntercept&&(a.fn.MultiFile.intercept(a.fn.MultiFile.options.autoIntercept),a.fn.MultiFile.options.autoIntercept=null),this.not(".MultiFile-applied").addClass("MultiFile-applied").each(function(){window.MultiFile=(window.MultiFile||0)+1;var d=window.MultiFile,e={e:this,E:a(this),clone:a(this).clone()},f=a.extend({},a.fn.MultiFile.options,c||{},(a.metadata?e.E.metadata():a.meta?e.E.data():null)||{},{});f.max>0||(f.max=e.E.attr("maxlength")),f.max>0||(f.max=(String(e.e.className.match(/\b(max|limit)\-([0-9]+)\b/gi)||[""]).match(/[0-9]+/gi)||[""])[0],f.max=f.max>0?String(f.max).match(/[0-9]+/gi)[0]:-1),f.max=new Number(f.max),f.accept=f.accept||e.E.attr("accept")||"",f.accept||(f.accept=e.e.className.match(/\b(accept\-[\w\|]+)\b/gi)||"",f.accept=new String(f.accept).replace(/^(accept|ext)\-/i,"")),f.maxsize=f.maxsize>0?f.maxsize:null||e.E.data("maxsize")||0,f.maxsize>0||(f.maxsize=(String(e.e.className.match(/\b(maxsize|maxload|size)\-([0-9]+)\b/gi)||[""]).match(/[0-9]+/gi)||[""])[0],f.maxsize=f.maxsize>0?String(f.maxsize).match(/[0-9]+/gi)[0]:-1),f.maxfile=f.maxfile>0?f.maxfile:null||e.E.data("maxfile")||0,f.maxfile>0||(f.maxfile=(String(e.e.className.match(/\b(maxfile|filemax)\-([0-9]+)\b/gi)||[""]).match(/[0-9]+/gi)||[""])[0],f.maxfile=f.maxfile>0?String(f.maxfile).match(/[0-9]+/gi)[0]:-1),f.maxfile>1&&(f.maxfile=1024*f.maxfile),f.maxsize>1&&(f.maxsize=1024*f.maxsize),f.max>1&&e.E.attr("multiple","multiple").prop("multiple",!0),a.extend(e,f||{}),e.STRING=a.extend(e.STRING||{},a.fn.MultiFile.options.STRING,e.STRING),a.extend(e,{n:0,slaves:[],files:[],instanceKey:e.e.id||"MultiFile"+String(d),generateID:function(a){return e.instanceKey+(a>0?"_F"+String(a):"")},trigger:function(b,c,d,e){var f,g=d[b]||d["on"+b];return g?(e=e||d.files||(this.files?this.files[0]:null)||[{name:this.value,size:0,type:((this.value||"").match(/[^\.]+$/i)||[""])[0]}],a.each(e,function(a,b){f=g(c,b.name,d,b)}),f):void 0}}),String(e.accept).length>1&&(e.accept=e.accept.replace(/\W+/g,"|").replace(/^\W|\W$/g,""),e.rxAccept=new RegExp("\\.("+(e.accept?e.accept:"")+")$","gi")),e.wrapID=e.instanceKey+"_wrap",e.E.wrap('<div class="MultiFile-wrap" id="'+e.wrapID+'"></div>'),e.wrapper=a("#"+e.wrapID),e.e.name=e.e.name||"file"+d+"[]",e.list||(e.wrapper.append('<div class="MultiFile-list" id="'+e.wrapID+'_list"></div>'),e.list=a("#"+e.wrapID+"_list")),e.list=a(e.list),e.addSlave=function(c,f){e.n++,c.MultiFile=e,f>0&&(c.id=c.name=""),f>0&&(c.id=e.generateID(f)),c.name=String(e.namePattern.replace(/\$name/gi,a(e.clone).attr("name")).replace(/\$id/gi,a(e.clone).attr("id")).replace(/\$g/gi,d).replace(/\$i/gi,f));var g;e.max>0&&e.files.length>e.max&&(c.disabled=!0,g=!0),e.current=e.slaves[f]=c,c=a(c),c.val("").attr("value","")[0].value="",c.addClass("MultiFile-applied"),c.change(function(){a(this).blur();var d=this,g=e.files||[],h=this.files||[{name:this.value,size:0,type:((this.value||"").match(/[^\.]+$/i)||[""])[0]}],i=[],j=0,k=e.total_size||0,l=[];a.each(h,function(a,b){i[i.length]=b}),e.trigger("FileSelect",this,e,i),a.each(h,function(a,c){var f=c.name,g=c.size,h=function(a){return a.replace("$ext",String(f.match(/[^\.]+$/i)||"")).replace("$file",f.match(/[^\/\\]+$/gi)).replace("$size",b(g)+" > "+b(e.maxfile))};e.accept&&f&&!f.match(e.rxAccept)&&(l[l.length]=h(e.STRING.denied),e.trigger("FileInvalid",this,e,[c]));for(var i in e.slaves)if(e.slaves[i]&&e.slaves[i]!=d){var k=(e.slaves[i].value||"").replace(/^C:\\fakepath\\/gi,"");(f==k||f==k.substr(k.length-f.length))&&(l[l.length]=h(e.STRING.duplicate),e.trigger("FileDuplicate",this,e,[c]))}e.maxfile>0&&g>0&&g>e.maxfile&&(l[l.length]=h(e.STRING.toobig),e.trigger("FileTooBig",this,e,[c]));var m=e.trigger("FileValidate",this,e,[c]);m&&""!=m&&(l[l.length]=h(m)),j+=c.size}),k+=j,i.size=j,i.total=k,i.total_length=i.length+g.length,e.max>0&&g.length+h.length>e.max&&(l[l.length]=e.STRING.toomany.replace("$max",e.max),e.trigger("FileTooMany",this,e,i)),e.maxsize>0&&k>e.maxsize&&(l[l.length]=e.STRING.toomuch.replace("$size",b(k)+" > "+b(e.maxsize)),e.trigger("FileTooMuch",this,e,i));var m=a(e.clone).clone();return m.addClass("MultiFile"),l.length>0?(e.error(l.join("\n\n")),e.n--,e.addSlave(m[0],f),c.parent().prepend(m),c.remove(),!1):(e.total_size=k,h=g.concat(i),h.size=k,h.size_label=b(k),e.files=h,a(this).css({position:"absolute",top:"-3000px"}),c.after(m),e.addSlave(m[0],f+1),e.addToList(this,f,i),e.trigger("afterFileSelect",this,e,i),void 0)}),a(c).data("MultiFile",e),g&&a(c).attr("disabled","disabled").prop("disabled",!0)},e.addToList=function(c,d,f){e.trigger("FileAppend",c,e,f);var g=a("<span/>");a.each(f,function(d,f){var h=String(f.name||""),i=e.STRING,j=i.label||i.file||i.name,k=i.title||i.tooltip||i.selected,l='<img class="MultiFile-preview" style="'+e.previewCss+'"/>',m=a(('<span class="MultiFile-label" title="'+k+'"><span class="MultiFile-title">'+j+"</span>"+(e.preview||a(c).is(".with-preview")?l:"")+"</span>").replace(/\$(file|name)/gi,(h.match(/[^\/\\]+$/gi)||[h])[0]).replace(/\$(ext|extension|type)/gi,(h.match(/[^\.]+$/gi)||[""])[0]).replace(/\$(size)/gi,b(f.size||0)).replace(/\$(preview)/gi,l));m.find("img.MultiFile-preview").each(function(){var a=this,b=new FileReader;b.readAsDataURL(f),b.onload=function(b){a.src=b.target.result}}),d>1&&g.append(", "),g.append(m)});var h=a('<div class="MultiFile-label"></div>'),i=a('<a class="MultiFile-remove" href="#'+e.wrapID+'">'+e.STRING.remove+"</a>").click(function(){e.trigger("FileRemove",c,e,h),e.n--,e.current.disabled=!1,e.slaves[d]=null,a(c).remove(),a(this).parent().remove(),a(e.current).css({position:"",top:""}),a(e.current).reset().val("").attr("value","")[0].value="";var b=[];for(var f in e.slaves){var g=e.slaves[f];if(null!=g&&void 0!=g){var h=(g.files&&g.files.length?g.files:null)||[{name:this.value,size:0,type:((this.value||"").match(/[^\.]+$/i)||[""])[0]}];a.each(h,function(a,c){void 0!=c.name&&(b[b.length]=c)})}}return e.files=b,e.trigger("afterFileRemove",c,e,h),e.trigger("FileChange",c,e,e.files),!1});e.list.append(h.append(i," ",g)),e.trigger("afterFileAppend",c,e,f),e.trigger("FileChange",c,e,e.files)},e.MultiFile||e.addSlave(e.e,0),e.n++,e.E.data("MultiFile",e)})},a.extend(a.fn.MultiFile,{reset:function(){var b=a(this).data("MultiFile");return b&&b.list.find("a.MultiFile-remove").click(),a(this)},files:function(){var a=this.data("MultiFile");return a?a.files||[]:!console.log("MultiFile plugin not initialized")},size:function(){var a=this.data("MultiFile");return a?a.total_size||[]:!console.log("MultiFile plugin not initialized")},count:function(){var a=this.data("MultiFile");return a?a.files.length||[]:!console.log("MultiFile plugin not initialized")},disableEmpty:function(b){b=("string"==typeof b?b:"")||"mfD";var c=[];return a("input:file.MultiFile").each(function(){""==a(this).val()&&(c[c.length]=this)}),window.clearTimeout(a.fn.MultiFile.reEnableTimeout),a.fn.MultiFile.reEnableTimeout=window.setTimeout(a.fn.MultiFile.reEnableEmpty,500),a(c).each(function(){this.disabled=!0}).addClass(b)},reEnableEmpty:function(b){return b=("string"==typeof b?b:"")||"mfD",a("input:file."+b).removeClass(b).each(function(){this.disabled=!1})},intercepted:{},intercept:function(b,c,d){var e,f;if(d=d||[],d.constructor.toString().indexOf("Array")<0&&(d=[d]),"function"==typeof b)return a.fn.MultiFile.disableEmpty(),f=b.apply(c||window,d),setTimeout(function(){a.fn.MultiFile.reEnableEmpty()},1e3),f;b.constructor.toString().indexOf("Array")<0&&(b=[b]);for(var g=0;g<b.length;g++)e=b[g]+"",e&&function(b){a.fn.MultiFile.intercepted[b]=a.fn[b]||function(){},a.fn[b]=function(){return a.fn.MultiFile.disableEmpty(),f=a.fn.MultiFile.intercepted[b].apply(this,arguments),setTimeout(function(){a.fn.MultiFile.reEnableEmpty()},1e3),f}}(e)}}),a.fn.MultiFile.options={accept:"",max:-1,maxfile:-1,maxsize:-1,namePattern:"$name",preview:!1,previewCss:"max-height:100px; max-width:100px;",STRING:{remove:"x",denied:"You cannot select a $ext file.\nTry again...",file:"$file",selected:"File selected: $file",duplicate:"This file has already been selected:\n$file",toomuch:"The files selected exceed the maximum size permited ($size)",toomany:"Too many files selected (max: $max)",toobig:"$file is too big (max $size)"},autoIntercept:["submit","ajaxSubmit","ajaxForm","validate","valid"],error:function(a){"undefined"!=typeof console&&console.log(a),alert(a)}},a.fn.reset=a.fn.reset||function(){return this.each(function(){try{this.reset()}catch(a){}})},a(function(){a("input[type=file].multi").MultiFile()})}(jQuery);