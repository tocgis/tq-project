 $("#flow-scorll-left p").click(function() {
     $(this).toggleClass('active');
     $("#flow-scorll-right p").removeClass('active');
 });

 $("#flow-scorll-right p").click(function() {
     $(this).toggleClass('active');
     $("#flow-scorll-left p").removeClass('active');
 });

 $("#transfer-left").bind('click', function() {
     $.each($("#flow-scorll-right p"), function(k, v) {
         if($(v).hasClass('active')) {
             $("#flow-scorll-left").append($(v));
         }
     });
 });

 $("#transfer-right").bind('click', function() {
     $.each($("#flow-scorll-left p"), function(k, v) {
         if($(v).hasClass('active')) {
             $("#flow-scorll-right").append($(v));
         }
     });
 });

 $("#flow-scorll-save").bind('click', function() {
     //保存
     var leftActive = []; // 左边的
     var rightActive = []; // 右边的
     $.each($("#flow-scorll-left p"), function(k, v) {
         leftActive.push($(v).html());
     });

     $.each($("#flow-scorll-right p"), function(k, v) {
         rightActive.push($(v).html());
     });
     console.log(leftActive);
     console.log(rightActive);
 });

 var nextStep = []; //下一步
 var personnel = {
     department: [],
     person: []
 }; //人员
 var department = []; //部门
 var role = []; //角色
 var arr = [nextStep, personnel, department, role];
 var idName = {
     nextStep: 'step',
     department: 'department_id',
     personnelid: 'staff_id',
     role: 'duty_id'
 };
 var peosonval = [];
 var getTypeData = {
     nextStep: function(ele) {
         var _html = '';
         _html = this.defaultFuc(nextStep, 'nextStep', '/admin/flow/flow_process_list_json?type_id=1', ele, _html);
         $(ele).children('.list-srcoll').children('.list-srcoll-con').html(_html);
         pinit($(ele).children('.list-srcoll').children('.list-srcoll-con').children('p'), 0);
     },
     personnel: function(ele) {
         var _html = '<p class="" data-id="0" id="alldata">全部</p>';
         _html = this.defaultFuc(personnel.department, 'department', '/admin/department/listJson', ele, _html);
         $(ele).children('.list-srcoll').children('.list-srcoll-con').eq(0).html(_html);
         personnerInit($(ele).children('.list-srcoll').children('.list-srcoll-con').eq(0).children('p'), 1);
     },
     department: function(ele) {
         var _html = '';
         _html = this.defaultFuc(department, 'department', '/admin/department/listJson', ele, _html);
         $(ele).children('.list-srcoll').children('.list-srcoll-con').html(_html);
         pinit($(ele).children('.list-srcoll').children('.list-srcoll-con').children('p'), 2);
     },
     role: function(ele) {
         var _html = '';
         _html = this.defaultFuc(role, 'role', '/admin/organization/duty_list_json', ele, _html);
         $(ele).children('.list-srcoll').children('.list-srcoll-con').html(_html);
         pinit($(ele).children('.list-srcoll').children('.list-srcoll-con').children('p'), 3);
     },
     defaultFuc: function(datname, idname, posturl, ele, _html) {

         if(datname.length == 0) {
             $.ajax({
                 type: "get",
                 url: posturl,
                 async: false,
                 dataType: 'JSON',
                 success: function(data) {

                     with(data) {
                         if(code == 200) {
                             for(var k in data) {
                                 data[k].active = '';
                                 datname.push(data[k]);
                                 _html += '<p class="' + data[k].active + '" data-id="' + data[k][idName[idname]] + '">' + data[k].title + '</p>';
                             }
                         }
                     }
                 },
                 error: function() {
                     console.log('失败');
                 }
             });

         } else {
             for(var k in datname) {
                 _html += '<p class="' + datname[k].active + '" data-id="' + datname[k][idName[idname]] + '">' + datname[k].title + '</p>';
             }
         }
         return _html;
     }
 }

 $(".from-dialog").bind('click', function() {
     $(this).next('.list-dialog').fadeToggle();
     $(this).parent().siblings().children('.list-dialog').fadeOut();
     var type = $(this).attr('data-type');
     var self = this;
     if(type != '') {
         getTypeData[type]($(self).next());
     }
 });

 $(".close-dialog").click(function() {
     $(this).parents('.list-dialog').fadeOut();
 });

 function pinit(ele, i) {
     //var index = nextStep[$(ele).index()];
     var checkType = $(".from-dialog").eq(i).attr('data-check');
     $(ele).each(function(k, v) {
         $(v).unbind('click').bind('click', function() {
             if(checkType == 'radio') {
                 $(this).siblings().removeClass('active');
             }
             $(this).toggleClass('active');
         });
     });
 }

 function personnerInit(ele, i) {
     if(personnel.person.length == 0) {
         getAllperson();
     };
     var checkType = $(".from-dialog").eq(i).attr('data-check');
     $(ele).each(function(k, v) {
         $(v).unbind('click').bind('click', function() {
             if(checkType == 'radio') {
                 $(this).siblings().removeClass('active');
             }
             $(this).toggleClass('active');
             var id = $(this).attr('data-id');
             if(id == 0) {
                 $("#con-1-2 p").show();
             } else {
                 $("#con-1-2 p").each(function() {
                     if($(this).attr('data-department') == id) {
                         $(this).show();
                     } else {
                         $(this).hide();
                     }
                 });
             }

         });
     });

     $("#alldata").click();

 }

 function getAllperson() {
     var _html = '';
     $.ajax({
         type: "get",
         url: '/admin/staff/list_json?department_id=0',
         async: false,
         dataType: 'JSON',
         success: function(data) {
             with(data) {
                 if(code == 200) {
                     for(var k in data) {
                         data[k].active = '';
                         personnel.person.push(data[k]);
                         _html += '<p class="" data-id="' + data[k][idName.personnelid] + '" data-department="' + data[k].department_id + '">' + data[k].realname + '</p>';
                     }
                 }
             }
             $("#con-1-2").html(_html);

             $("#con-1-2 p").click(function() {
                 $(this).toggleClass('active');

             });
         },
         error: function() {
             console.log('失败');
         }
     });

 }

 $(".save-from").click(function() {
     var thisarr = arr[$(this).attr('data-num')];
     var hdObj = $(this).attr('data-typeid');
     var htmlinner = '';
     var idinner = '';
     $("#con-" + $(this).attr('data-num')).children('p').each(function(k, v) {
         if($(this).hasClass('active')) {
             thisarr[$(v).index()].active = 'active';
             htmlinner += $(this).html() + ',';
             idinner += $(this).attr('data-id') + ',';
         } else {
             thisarr[$(v).index()].active = '';
         }
     });
     htmlinner = htmlinner.substr(0, htmlinner.length - 1);
     idinner = idinner.substr(0, idinner.length - 1);
     $("#" + hdObj).val(htmlinner);
     $("#" + hdObj).attr('data-id', idinner);
     $(this).parents('.list-dialog').fadeOut();
 });

 $("#sava-proson").click(function() {
     var names = '';
     $("#con-1-2 p").each(function(k, v) {
         if($(v).hasClass('active')) {
             var val = {
                 department: $(v).attr('data-department'),
                 id: $(v).attr('data-id')
             };
             names += $(v).html() + ','
             peosonval.push(val);
         }
     });
     $("#" + $(this).attr('data-typeid')).val(names.substr(0, names.length - 1));
     $(this).parents('.list-dialog').fadeOut();
 });