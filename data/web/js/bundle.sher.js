var sher={visitor:{},url:{},redirect:function(b,a){setTimeout(function(){window.location=b},a)},show_error_note:function(b,a){sher.show_notify_bar(b,"error",a)},show_ok_note:function(b,a){sher.show_notify_bar(b,"ok",a)},show_notify_bar:function(d,b,a){var c;if(!b||b=="ok"){b="ok";c="alert-success"}else{b="error";c="alert-danger"}$.show_notify_bar({position:"top",removebutton:true,message:d,time:a,class_name:c,container:"#alert-box"})}};sher.initial=function(){$("body").on("click","a.confirm-request",function(){if(confirm("确认执行这个操作吗?")){$.get($(this).attr("href"))}return false});$("body").on("click","a.ajax-request",function(){$.get($(this).attr("href"));return false});$("body").on("click","a.submit-button",function(){try{$(this).parent("form").submit()}catch(e){}return false});$("body").on("click","a.ajax-hash",function(){var hash=this.hash&&this.hash.substr(1);if(hash!=""){eval(hash+".call(this);")}return false});$("form.ajax-form").livequery(function(){$(this).ajaxForm()});$("body").on("click","a.close",function(){try{$(this).parent().fadeOut()}catch(e){}return false});sher.showbox();sher.hide_cake_box()};sher.showbox=function(){$("body").on("click",".showbox",function(){var a=this.hash&&this.hash.substr(1);$target=$("#"+a);if($target.hasClass("show")){$target.slideUp("slow",function(){$target.removeClass("show")})}else{$target.slideDown("slow",function(){$(this).addClass("show")})}return false})};sher.hide_cake_box=function(){setTimeout(function(){$("#cake-box").fadeOut("slow")},10000)};sher.build_auth_page=function(){$("#login-form").validate({rules:{account:{required:true},password:{required:true,minlength:6}},messages:{account:"邮件格式不对,请输入您注册时填写的邮件",password:"请输入正确的登录密码（6位以上字符)"},submitHandler:function(a){$(a).ajaxSubmit()}});$("#register-form").validate({onkeyup:false,focusCleanup:true,rules:{account:{required:true},password:{required:true},password_confirm:{equalTo:"#password",required:true},invite_code:{required:true}},messages:{account:{required:"请填写你的常用手机号码",},password:{required:"密码还没有填写",minlength:jQuery.validator.format("密码长度需要{0}位以上")},password_confirm:{required:"确认密码还没有填写",minlength:jQuery.validator.format("密码长度需要{0}位以上"),equalTo:"两次输入密码不一致"},invite_code:{required:"请提供有效的邀请码"}},errorPlacement:function(a,b){$(b).parent().append(a)},submitHandler:function(a){$(a).ajaxSubmit()}})};sher.build_profile_page=function(){$("#profile-form").validate({onkeyup:false,focusCleanup:true,rules:{nickname:{required:true},sex:{required:true},marital:{required:true},realname:{required:true},year:{required:true},mouth:{required:true},day:{required:true}},messages:{nickname:{required:"请填写你的昵称",},sex:{required:"请选择你的性别",},marital:{required:"请选择你的婚姻状况",},realname:{required:"请填写你的真实姓名",},year:{required:"请填写你的出生年份",},mouth:{required:"请填写你的出生月份",},day:{required:"请填写你的出生日期",},},errorPlacement:function(a,b){$(b).parent().append(a)},submitHandler:function(a){$(a).ajaxSubmit()}})};var wps_width=0,wps_height=0,wps_ratio=1;var scale_width=580,scale_height=0;var crop_width=0,crop_height=0;sher.hook_imgarea_select=function(){scale_height=parseInt(wps_height*scale_width/wps_width);ias=$("img#avatar-photo").imgAreaSelect({aspectRatio:"1:1",x1:0,y1:0,x2:290,y2:290,handles:true,fadeSpeed:200,instance:true,onSelectChange:sher.preview,onSelectEnd:sher.updateAreaSelect})};sher.preview=function(a,b){if(!b.width||!b.height){return}$("#x1").val(b.x1);$("#y1").val(b.y1);$("#x2").val(b.x2);$("#y2").val(b.y2);$("#w").val(b.width);$("#h").val(b.height)};sher.updateAreaSelect=function(){};$.extend($.imgAreaSelect.prototype,{animateSelection:function(b,d,a,c,f){var e=$.extend($("<div/>")[0],{ias:this,start:this.getSelection(),end:{x1:b,y1:d,x2:a,y2:c}});$(e).animate({cur:1},{duration:f,step:function(l,m){var n=m.elem.start,i=m.elem.end,h=Math.round(n.x1+(i.x1-n.x1)*l),k=Math.round(n.y1+(i.y1-n.y1)*l),g=Math.round(n.x2+(i.x2-n.x2)*l),j=Math.round(n.y2+(i.y2-n.y2)*l);m.elem.ias.setSelection(h,k,g,j);m.elem.ias.update()}})}});