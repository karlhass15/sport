/**
 * Copyright © 2017 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
var QuestionInApprModal;
var ItorisHelper={
    queryParams : {},
    hashRegex : /qa\[(([^\]])|(\]=))*\]/,
    flagNotClearQueryParams : false,
    config : {},
    keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    searchField: null,
    encode64 : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
                this.keyStr.charAt(enc1) + this.keyStr.charAt(enc2) +
                this.keyStr.charAt(enc3) + this.keyStr.charAt(enc4);
        }
        return output;
    },
    decode64 : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = this.keyStr.indexOf(input.charAt(i++));
            enc2 = this.keyStr.indexOf(input.charAt(i++));
            enc3 = this.keyStr.indexOf(input.charAt(i++));
            enc4 = this.keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        return output;
    },
    getQaHash : function() {
        var origHash = window.location.hash;
        var anchor = window.location.hash ? window.location.hash.substring(1) : '';
        var qaAnchor = anchor.match(this.hashRegex);
        return qaAnchor ? qaAnchor[0] : null;
    },
    removeUrl:function(param,modesUrl,mode){
        var str =  window.location.hash;
        var arrUrl = str.split(modesUrl);
        var bool=false;
        var newParamArray=[];
        arrUrl = this.decode64(arrUrl[1]);
        var arrUrl = arrUrl.split('&')

        for (var i = 0; i < arrUrl.length; i++){
            if(arrUrl[i].substr(0,mode.length) == mode ){
                bool=true;
                continue;
            }
            newParamArray.push(arrUrl[i])
        }
        if(bool){
            newParamArray=newParamArray.join('&');
        }else {
            newParamArray=arrUrl.join('&');
        }
        var hashParamsQa = 'qa['+modesUrl+ this.encode64(newParamArray) +modesUrl+ ']';
        window.location.hash = hashParamsQa;
        return urlModeQA+'?'+newParamArray;
    },
    removeUrlAccordion:function(param,mode,modesUrl,accordionName,value){
        var str =  window.location.hash;
        var arrUrl = str.split(modesUrl);
        var bool=false;
        var newParamArray=[];
        arrUrl = this.decode64(arrUrl[1]);
        var arrUrl = arrUrl.split('&')

        for (var i = 0; i < arrUrl.length; i++){
            if(arrUrl[i].substr(0,mode.length) == mode ){
                if(value &&  accordionName == mode){
                    var acc = this.getParamAccordion(modesUrl,accordionName);
                    var newParams=[];
                    if(acc && acc.length>1){
                        for(var ac=0;ac<acc.length; ac++){
                            if(acc[ac] ==value){
                                continue;
                            }
                            newParams.push(acc[ac]);
                        }
                        arrUrl[i]=mode+'='+newParams.join(',');
                    }else {
                        bool=true;
                        continue;
                    }
                }else{
                    bool=true;
                    continue;
                }

            }
            newParamArray.push(arrUrl[i])
        }
        if(bool){
            newParamArray=newParamArray.join('&');
        }else {
            newParamArray=arrUrl.join('&');
        }
        var hashParamsQa = 'qa['+modesUrl+ this.encode64(newParamArray) +modesUrl+ ']';
        window.location.hash = hashParamsQa;
        return urlModeQA+'?'+newParamArray;
    },
    updateHash : function(params,mode) {
        var hash = this.getQaHash();
        var params =params;
        var hashParamsQa = 'qa['+mode+ this.encode64(params) +mode+ ']';
        window.location.hash = hashParamsQa;
    },
    isAccordionOne:function(mode,nameParams){
        var urlDecode = this.getUrlDecode('mode');
        urlDecode = urlDecode.split('&');
        if(urlDecode.length==1 &&  urlDecode.substr(0,nameParams.length)==nameParams){
                        return true;
        }
        return false;
    },
    getUrlDecode:function(mode){
        var str =  window.location.hash;
        var arrUrl = str.split(mode);
      return  arrUrl = this.decode64(arrUrl[1]);
    },
    getUrl:function(mode){
        var arrUrl = this.getUrlDecode(mode);
       return urlModeQA+'?'+arrUrl;
    },
    ismodeAction:function(mode){
        var str = this.getQaHash();
        var arrUrlmode = str.split(mode);
        if(arrUrlmode.length>0){
            return true;
        }else{
           return false;
        }

    },
    getParamAccordion:function(mode,accordionName){
        var arrUrl = this.getUrlDecode(mode);
        arrUrl = arrUrl.split('&');
        for (var i = 0; i < arrUrl.length; i++){
            if(arrUrl[i].substr(0,accordionName.length) == accordionName ){
                var params = arrUrl[i].split('=');
                params = params[1].split(',');
                return [params[params.length-1]];
            }

        }
        return false;

    },
    getParam:function(mode,maskParams){
        var arrUrl = this.getUrlDecode(mode);
        arrUrl = arrUrl.split('&');
        params=[];
        for (var i = 0; i < arrUrl.length; i++){
            if(arrUrl[i].substr(0,maskParams.length) == maskParams ){
                var matchStrLength = arrUrl[i].split('=')
                if(matchStrLength[0].length==maskParams.length) {
                    var params = arrUrl[i].split('=');
                    params = params[1];
                    return params;
                }
            }

        }
        return false;
    },
    urlUpdate:function(param,modesUrl,mode,accordionNum,accordionName){
        var str =  window.location.hash;
        var arrUrl = str.split(modesUrl);
        var bool=false;
        arrUrl = this.decode64(arrUrl[1]);
        var arrUrl = arrUrl.split('&')
         var  newParams=[];
        for (var i = 0; i < arrUrl.length; i++){
            var valueStr = arrUrl[i].split('=');
            if(arrUrl[i].substr(0,mode.length) == mode &&  valueStr[0].length==mode.length){
                if(accordionNum && accordionName.substr(0,mode.length)==mode){
                        str = 'accordion='+accordionNum;
                    newParams.push(str);
                    bool=true;
                    continue;
                }
                bool= true;
                newParams.push(param);
               continue;
            }
            newParams.push(arrUrl[i])
        }
        if(accordionName && accordionNum && !this.getParamAccordion(modesUrl,accordionName) && bool){
            newParams[newParams.length+1]=accordionName+'='+accordionNum;
        }else if(accordionName && accordionNum && !this.getParamAccordion(modesUrl,accordionName)){
            arrUrl[arrUrl.length+1]=accordionName+'='+accordionNum;
        }
        if(bool){
            arrUrl=newParams.join('&');
        }else {
            arrUrl=arrUrl.join('&');
            arrUrl = arrUrl+'&'+param;
        }
        var hashParamsQa = 'qa['+modesUrl+ this.encode64(arrUrl) +modesUrl+ ']';

        window.location.hash = hashParamsQa;
        return urlModeQA+'?'+arrUrl;

    }
}
requirejs(['jquery','mage/translate','Magento_Ui/js/modal/modal','mage/mage'],function($,$t){
    var dataForm = [];
    var ignore = null;
    var selectAttr = '#productqa.tab';
    $('.itorisis_action_view').click(function(event){
        event.preventDefault();
        $('#productQaContainer').closest('[data-role="content"]').prev().click();
        //$('a[href="'+selectAttr+'"]').click();
        $('html:not(:animated), body:not(:animated)').animate({
            scrollTop:  $('#productQaContainer').offset().top
        }, 650);
    });
    $('.itoris-action-add').click(function(event){
        event.preventDefault();
        if(canPost){
            window.location.href = canPost;
            return ;
        }
        $('#productQaContainer').closest('[data-role="content"]').prev().click();
        //$('a[href="'+selectAttr+'"]').click();
        $('.itoris-ask-div.ask_questions .button.show_form.button_ask_questions.action.primary').click();
        $('html:not(:animated), body:not(:animated)').animate({
            scrollTop:  $('#itoris_qa_form_add_question').offset().top
        }, 650);
    });
    if(!(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]'))){
        dataForm =$('#itoris_qa_form_add_question,.form-answerqa');
        dataForm.mage('validation', {
            required:true,
            'validate-length':true,
            ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
        }).find('input:text').attr('autocomplete', 'off');
         $('.itoris-container-accordion-div').show();
        $('#empty-question-qa-product').show();
        $('#itoris_qa_pages').show();
    }

    if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){

        var loader = $('.loading-mask');
        loader.css('display','block');
        var hash = window.location.hash
        var paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
        var urlModeThis = ItorisHelper.getUrl('[m=]');
        $('a[href="'+selectAttr+'"]')[0].click();

        setTimeout(function(){
            window.location.hash=hash;
            $.ajax({
                url: urlModeThis,
                dataType: 'html',
                showLoader: true,
                complete: function (data) {
                    $('.container-accordion').html(data.responseText);
                    dataForm =$('#itoris_qa_form_add_question,.form-answerqa');
                    dataForm.mage('validation', {
                        ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                    }).find('input:text').attr('autocomplete', 'off');
                    loader.hide();
                    $('.itoris-container-accordion-div').show();
                    $('#itoris_qa_pages').show();
                    $('#empty-question-qa-product').show();
                    if(paramsAcc && paramsAcc.length>1){
                        for(var ac=0;ac<paramsAcc.length; ac++){
                            $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                        }
                    }else {
                        $('#'+paramsAcc[0]).removeClass('ac_hidden');
                    }
                    var strSearch = false;
                    strSearch = ItorisHelper.getParam('[m=]','s');
                    if(strSearch){
                        $('#input-qa-search').val(strSearch);
                    }
                    var selectOrder = ItorisHelper.getParam('[m=]','mode');
                    if(selectOrder){
                        $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                    }
                }
            });
        },1000)

    }

    QuestionInApprModal=$('#question_inappr_popup_product').modal({
        type: 'popup',
        buttons: [],
    });
   $('.itoris-container-accordion').on('click','.itoris-accordion-h1 .icon-header-float-rigth .icon_good',function(event){
       event.stopPropagation();
      var idquest = $(this).attr('data-question-id');
       $.ajax({
           url: urlQuestionRatingPlus,
           data: {id:idquest},
           type: "POST",
           dataType: 'json',
           showLoader: true,
           complete: function (data) {
               if(data.responseJSON && data.responseJSON.url){
                   window.location.href = data.responseJSON.url;
                   return;
               }
               if(data.responseJSON && data.responseJSON.success){
                   $('.span-good-'+idquest).text(data.responseJSON.count);
               }
           }
       });
   });
    $('.itoris-container-accordion').on('click','.itoris-accordion-h1 .icon-header-float-rigth .icon_bad',function(event){

        var idquest = $(this).attr('data-question-id');
        $.ajax({
            url: urlQuestionRatingMinus,
            data: {id:idquest},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.url){
                    window.location.href = data.responseJSON.url;
                    return;
                }
                if(data.responseJSON && data.responseJSON.success){
                    $('.span-bad-'+idquest).text(data.responseJSON.count);
                }
            }
        });
    });
    $('.itoris-container-accordion').on('click','.itoris-accordion-h1 .icon_inappr',function(event){
        var idquest = $(this).attr('data-question-id');
        $.ajax({
            url: urlQuestionInappr,
            data: {id:idquest},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.url){
                    window.location.href = data.responseJSON.url;
                    return;
                }
                if(data.responseJSON && data.responseJSON.message){
                    QuestionInApprModal.html(data.responseJSON.message)
                    QuestionInApprModal.modal('openModal');
                }

            }
        });
    });
    $(function() {
        $('.ask-button-submit').click(function(){
            /*if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){
                ItorisHelper.urlUpdate('accordion='+modeQa,'[m=]','mode');
            }else{
                ItorisHelper.updateHash('mode='+modeQa+'&page='+pageQA+'&product_id='+productIdQ+'&pages='+pages+'&per_page='+perPage+'&store_id='+store_id_qa,'[m=]');
            }*/
            if(!$(this).hasClass('expanded-class')){
                $('.itoris-container-accordion-div section').removeClass('ac_hidden');
                $(this).text('-');
                $(this).addClass('expanded-class');
            }else{
                $('.itoris-container-accordion-div section').addClass('ac_hidden');
                $(this).text('+');
                $(this).removeClass('expanded-class');
            }

        });
        $('.itoris-container-accordion').on('click','.itoris-button-subscribe-q',function(){
            var idquest = $(this).attr('data-question-id');
            var self =$(this);
            var email = $('#itoris-input-email'+idquest).val();
            if(email) {
                $.ajax({
                    url: urlSubscribedQuestion,
                    data: {question_id: idquest, email: email},
                    type: "POST",
                    dataType: 'json',
                    showLoader: true,
                    complete: function (data) {
                        if (data.responseJSON && data.responseJSON.message) {
                            QuestionInApprModal.html(data.responseJSON.message)
                            QuestionInApprModal.modal('openModal');
                            $('#itoris-input-email'+idquest).val('');
                        } else if (data.responseJSON && data.responseJSON.error) {
                            QuestionInApprModal.html(data.responseJSON.error)
                            QuestionInApprModal.modal('openModal');
                        }
                    }
                });
            }else{
                $.ajax({
                    url: urlSubscribedQuestion,
                    data: {question_id: idquest},
                    type: "POST",
                    dataType: 'json',
                    showLoader: true,
                    complete: function (data) {
                        if (data.responseJSON && data.responseJSON.message) {
                            self.text($t('Unsubscribe'));
                            self.removeClass('itoris-button-subscribe-q');
                            self.addClass('itoris-button-unsubscribe-q');
                            QuestionInApprModal.html(data.responseJSON.message)
                            QuestionInApprModal.modal('openModal');
                        } else if (data.responseJSON && data.responseJSON.error) {
                            QuestionInApprModal.html(data.responseJSON.error)
                            QuestionInApprModal.modal('openModal');
                        }
                    }
                });
            }
        });
        $('.qa_subscribe_box').on('click','.itoris-button-unsubscribe-q',function(){
            var idquest = $(this).attr('data-question-id');
            var self = $(this);
            var email = $('itoris-input-email'+idquest).val();
            if(email) {
                $.ajax({
                    url: urlUnscribeQuestion,
                    data: {question_id: idquest,email:email},
                    type: "POST",
                    dataType: 'json',
                    showLoader: true,
                    complete: function (data) {
                        if (data.responseJSON && data.responseJSON.message) {
                            QuestionInApprModal.html(data.responseJSON.message)
                            QuestionInApprModal.modal('openModal');
                        } else if (data.responseJSON && data.responseJSON.error) {
                            QuestionInApprModal.html(data.responseJSON.error)
                            QuestionInApprModal.modal('openModal');
                        }
                    }
                });
            }else{
                $.ajax({
                    url: urlUnscribeQuestion,
                    data: {question_id: idquest},
                    type: "POST",
                    dataType: 'json',
                    showLoader: true,
                    complete: function (data) {
                        if (data.responseJSON && data.responseJSON.message) {
                            self.text($t('Subscribe'));
                            self.removeClass('itoris-button-unsubscribe-q');
                            self.addClass('itoris-button-subscribe-q');
                            QuestionInApprModal.html(data.responseJSON.message)
                            QuestionInApprModal.modal('openModal');
                        } else if (data.responseJSON && data.responseJSON.error) {
                            QuestionInApprModal.html(data.responseJSON.error)
                            QuestionInApprModal.modal('openModal');
                        }
                    }
                });
            }
        });
        $(".itoris-container-accordion").on('click','section h1',function(event) {
            event.preventDefault();
            if(event.target.className!=='icon_inappr' && event.target.className!=='icon_bad' && event.target.className!=='icon_good') {
                if (!$(this).parents('section').hasClass('ac_hidden')) {
                    $(this).parents('section').addClass("ac_hidden");
                    ItorisHelper.removeUrlAccordion('accordion' + $(this).attr('data_accordion_id'), 'accordion', '[m=]', 'accordion', $(this).attr('data_accordion_id'));

                } else {
                    if (ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')) {
                        ItorisHelper.urlUpdate('accordion=', '[m=]', 'accordion', $(this).attr('data_accordion_id'), 'accordion');
                    } else {
                        ItorisHelper.updateHash('mode=' + modeQa + '&page=' + pageQA + '&product_id=' + productIdQ + '&pages=' + pages + '&per_page=' + perPage + '&store_id=' + store_id_qa + '&accordion=' + $(this).attr('data_accordion_id'), '[m=]');
                    }
                    $(this).parents("section").removeClass("ac_hidden");
                }
            }


        });
        $('.itoris-container-accordion').on('click','.button_ask_questions.show_form',function(e){
                    if(canPost){
                        window.location.href=canPost;
                        return;
                    }
                    $('#itoris_qa_add_question').show(500);
                    $('.itoris-ask-div').hide();
                    $('#itoris_qa_add_question .captcha-reload').click();
        });
        $('.button-div-hideform_question').on('click','.button_ask_questions.button_hide_form',function(){
            $('#itoris_qa_form_add_question')[0].reset();
            $('#itoris_qa_add_question').hide(500);
            $('.itoris-ask-div').show();
            $('.notify-email').hide();
            $('.newsletter-email').hide();
            $('.notify-itoris-qa .span-required-qa').hide();
            $('.newstler-div-itoris-qa .span-required-qa').hide();
            $('#itoris_qa_form_add_question .class-counter-question-global').text(0);
            $('.itoris-ask-div').show();
        });
    });

    $('#itoris_qa_select').on('change','#itoris_qa_select_menu',function(){
        var paramsAcc = [];
        modeQa =$('#itoris_qa_select_menu option:selected').val();
       if( parseInt($('#itoris_qa_select_menu option:selected').val())!=0) {
           if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){
               ItorisHelper.urlUpdate('mode='+modeQa,'[m=]','mode');
               ItorisHelper.urlUpdate('page='+pageQA,'[m=]','page');
               paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
           }else{
               ItorisHelper.updateHash('mode='+modeQa+'&page='+pageQA+'&product_id='+productIdQ+'&pages='+pages+'&per_page='+perPage+'&store_id='+store_id_qa,'[m=]');
           }

           $.ajax({
               url: ItorisHelper.getUrl('[m=]'),
               dataType: 'html',
               showLoader: true,
               complete: function (data) {
                   var strSearch = false;
                   strSearch = ItorisHelper.getParam('[m=]','s');
                   if(strSearch){
                       $('#input-qa-search').val(strSearch);
                   }
                   var selectOrder = ItorisHelper.getParam('[m=]','mode');
                   if(selectOrder){
                       $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                   }
                   $('.container-accordion').html(data.responseText);
                   $('.itoris-container-accordion-div').show();
                   $('#itoris_qa_pages').show();
                   if(paramsAcc && paramsAcc.length>1){
                       for(var ac=0;ac<paramsAcc.length; ac++){
                           $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                       }
                   }else {
                       $('#'+paramsAcc[0]).removeClass('ac_hidden');
                   }

               }
           });
       }
    });
    $('#itoris_qa_search_reset').click(function(){
        if(!ItorisHelper.getQaHash()){
            return;
        }
        $('#input-qa-search').val('');
        ItorisHelper.removeUrl('s='+$('#input-qa-search').val(),'[m=]','s');
        $.ajax({
            url: ItorisHelper.getUrl('[m=]'),
            dataType: 'html',
            showLoader: true,
            complete: function (data) {
                var strSearch = false;
                strSearch = ItorisHelper.getParam('[m=]','s');
                if(strSearch){
                    $('#input-qa-search').val(strSearch);
                }
                var selectOrder = ItorisHelper.getParam('[m=]','mode');
                if(selectOrder){
                    $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                }
                $('.container-accordion').html(data.responseText);
                $('.itoris-container-accordion-div').show();
                $('#itoris_qa_pages').show();
                var paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
                if(paramsAcc && paramsAcc.length>1){
                    for(var ac=0;ac<paramsAcc.length; ac++){
                        $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                    }
                }else {
                    $('#'+paramsAcc[0]).removeClass('ac_hidden');
                }


            }
        });
    });
    $('#input-qa-search').keyup(function(){
        if(event.keyCode==13)
        {
            $('#itoris_qa_search_go').click();
        }
    })
    $('#itoris_qa_search_go').click(function(){
        if($('#input-qa-search').val()=='' && !ItorisHelper.getQaHash()){
            return;
        }else if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){

            ItorisHelper.urlUpdate('s='+$('#input-qa-search').val(),'[m=]','s');
            ItorisHelper.urlUpdate('page='+pageQA,'[m=]','page');
        }else {
            ItorisHelper.updateHash('mode='+modeQa+'&page='+pageQA+'&product_id='+productIdQ+'&pages='+pages+'&per_page='+perPage+'&store_id='+store_id_qa+'&s='+$('#input-qa-search').val(),'[m=]');
        }
        $.ajax({
            url: ItorisHelper.getUrl('[m=]'),
            dataType: 'html',
            showLoader: true,
            complete: function (data) {
                var strSearch = false;
                strSearch = ItorisHelper.getParam('[m=]','s');
                if(strSearch){
                    $('#input-qa-search').val(strSearch);
                }
                var selectOrder = ItorisHelper.getParam('[m=]','mode');
                if(selectOrder){
                    $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                }
                $('.container-accordion').html(data.responseText);
                $('.itoris-container-accordion-div').show();
                $('#itoris_qa_pages').show();
                var paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
                if(paramsAcc && paramsAcc.length>1){
                    for(var ac=0;ac<paramsAcc.length; ac++){
                        $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                    }
                }else {
                    $('#'+paramsAcc[0]).removeClass('ac_hidden');
                }


            }
        });
    });
    $('#itoris_qa_notify').click(function(){
        if($(this).prop('checked')){
            $('.notify-email').show();
            $(this).next().show();
        }else{
            $('.notify-email').hide();
            $(this).next().hide();
        }
    });
    $('.itoris-newsletter').click(function(){
        if($(this).prop('checked')){
            $('.newsletter-email').show();
            $(this).next().show();
        }else{
            $('.newsletter-email').hide();
            $(this).next().hide();
        }
    });
    $('.button_add_question').click(function(){
        var paramsAcc = [];
        if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){
            paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
        }
        var elem = $('#itoris_qa_form_add_question .captcha-reload').last();
        if ($('#itoris_qa_form_add_question').valid() == true) {
            $.ajax({
                url: urlAddQuestion,
                data: $('#itoris_qa_form_add_question').serializeArray(),
                type: "POST",
                dataType: 'json',
                showLoader: true,
                complete: function (data) {
                    if(data.responseJSON && data.responseJSON.url){
                        window.location.href = data.responseJSON.url;
                        return;
                    }
                    canPost=false;
                    var messages='';
                    if(data.responseJSON && data.responseJSON.messagess){
                        messages=data.responseJSON.messagess;
                    }
                    if(ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')){
                        var strSearch = false;
                        strSearch = ItorisHelper.getParam('[m=]','s');
                        if(strSearch){
                            $('#input-qa-search').val(strSearch);
                        }
                        var selectOrder = ItorisHelper.getParam('[m=]','mode');
                        if(selectOrder){
                            $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                        }
                        var text = data.responseText;
                        if(data.responseJSON && data.responseJSON.html){
                            var messagessnomoder='';
                            if(data.responseJSON.messagessnomoder)
                            messagessnomoder= data.responseJSON.messagessnomoder;
                            var loader = $('.loading-mask');
                            loader.css('display','block');
                            var paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
                            var urlModeThis = ItorisHelper.getUrl('[m=]');
                            $.ajax({
                                url: urlModeThis,
                                dataType: 'json',
                                showLoader: true,
                                complete: function (data) {
                                    $('.container-accordion').html(data.responseText);
                                    loader.hide();
                                    var dataForm =$('.form-answerqa');
                                    dataForm.mage('validation', {
                                        required:true,
                                        'validate-length':true,
                                        ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                                    }).find('input:text').attr('autocomplete', 'off');
                                    $('.itoris-container-accordion-div').show();
                                    $('#itoris_qa_pages').show();
                                    if (ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')) {
                                        if (paramsAcc && paramsAcc.length && paramsAcc.length > 1) {
                                            for (var ac = 0; ac < paramsAcc.length; ac++) {
                                                $('#' + paramsAcc[ac]).removeClass('ac_hidden');
                                            }
                                        } else {
                                            $('#' + paramsAcc[0]).removeClass('ac_hidden');
                                        }
                                    }
                                    countQuestion = countQuestion+1;
                                    $('.itoris_counter_tab_count').html(countQuestion);
                                    $('.itoris-question-count').html(countQuestion);
                                    if(messagessnomoder){
                                        QuestionInApprModal.html(messagessnomoder);
                                        QuestionInApprModal.modal('openModal');
                                        $('#itoris_qa_form_add_question')[0].reset();
                                        $('#itoris_qa_add_question').hide(500);
                                        $('.itoris-ask-div').show();
                                        $('.notify-email').hide();
                                        $('.newsletter-email').hide();
                                        $('.notify-itoris-qa .span-required-qa').hide();
                                        $('.newstler-div-itoris-qa .span-required-qa').hide();
                                        $('#itoris_qa_form_add_question .class-counter-question-global').text(0);
                                    }
                                }

                            });

                        }else if(data.responseText!='') {
                            if(messages){
                                QuestionInApprModal.html(messages);
                                QuestionInApprModal.modal('openModal');
                                $('#itoris_qa_form_add_question')[0].reset();
                                $('#itoris_qa_add_question').hide(500);
                                $('.itoris-ask-div').show();
                                $('.notify-email').hide();
                                $('.newsletter-email').hide();
                                $('.notify-itoris-qa .span-required-qa').hide();
                                $('.newstler-div-itoris-qa .span-required-qa').hide();
                                $('#itoris_qa_form_add_question .class-counter-question-global').text(0);
                                $('.itoris-ask-div').show();
                            }else {
                                QuestionInApprModal.html(text);
                                QuestionInApprModal.modal('openModal')
                            }
                        }

                    }
                    else {
                        var htmlQuest = '';
                        if (data.responseJSON && data.responseJSON.html) {
                            htmlQuest = data.responseJSON.html;
                            $('.container-accordion').html(htmlQuest);
                            $('.itoris-container-accordion-div').show();
                            $('#itoris_qa_pages').show();
                            countQuestion = countQuestion+1;
                            $('.itoris_counter_tab_count').html(countQuestion);
                            $('.itoris-question-count').html(countQuestion);
                            if(data.responseJSON.messagessnomoder)
                                messagessnomoder= data.responseJSON.messagessnomoder;
                            if(messagessnomoder){
                                QuestionInApprModal.html(messagessnomoder);
                                QuestionInApprModal.modal('openModal');
                                $('#itoris_qa_form_add_question')[0].reset();
                                $('#itoris_qa_add_question').hide(500);
                                $('.itoris-ask-div').show();
                                $('.newsletter-email').hide();
                                $('.notify-email').hide();
                                $('.notify-itoris-qa .span-required-qa').hide();
                                $('.newstler-div-itoris-qa .span-required-qa').hide();
                                $('#itoris_qa_form_add_question .class-counter-question-global').text(0);
                            }

                        } else if (data.responseText) {
                            if(messages){
                                QuestionInApprModal.html(messages);
                                QuestionInApprModal.modal('openModal')
                            }else {
                                QuestionInApprModal.html(data.responseText);
                                QuestionInApprModal.modal('openModal')
                            }

                        }
                        if (ItorisHelper.getQaHash() && ItorisHelper.ismodeAction('[m=]')) {
                            if (paramsAcc && paramsAcc.length > 1) {
                                for (var ac = 0; ac < paramsAcc.length; ac++) {
                                    $('#' + paramsAcc[ac]).removeClass('ac_hidden');
                                }
                            } else if (paramsAcc.length == 1) {
                                $('#' + paramsAcc[0]).removeClass('ac_hidden');
                            }
                        }
                    }
                    elem.click();
                }
            });
        }else{
            $('.itoris_producttabs_form').submit();
        }
    });
    $('.itoris-container-accordion ').on('input propertychange','.form_box_answer .itoris_answer_textarea',function(){
       var idQuest = $(this).attr('data-question-id');
        $('#itoris_div_conter_'+idQuest+' .class-counter-question').text($(this).val().length)
    });
    $('#productQaContainer').on('input propertychange','#question_text',function(){
        $('.itoris_qa_counter_div_answer .class-counter-question-global').text($(this).val().length)
    });
    $('.itoris-container-accordion').on('click','.form-answerqa .captcha-reload',function(){
        $.ajax({
            url: window.captchaUrlRefresh,
            data: {formId: 'itoris_answer_captcha'},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.imgSrc){
                    $('.itoris-container-accordion .form-answerqa .captcha-image img').prop('src',data.responseJSON.imgSrc)
                }
            }
        });
    });
    $('.itoris-container-accordion ').on('click','.form_box_answer .form-answerqa button.button_answer_question.action.primary',function(){
        var idquest = $(this).attr('data-id-q-a');
        var paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion');
        if ($('#form-itoris-answer-'+idquest).valid() == true) {
            $.ajax({
                url: urlAddAnswer,
                type: "POST",
                data:$('#form-itoris-answer-'+idquest).serializeArray(),
                dataType: 'json',
                showLoader: true,
                complete: function (data) {
                    if(data.responseJSON && data.responseJSON.url){
                        window.location.href = data.responseJSON.url;
                        return;
                    }
                    if(paramsAcc && paramsAcc.length>1){
                        for(var ac=0;ac<paramsAcc.length; ac++){
                            $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                        }
                    }else {
                        $('#'+paramsAcc[0]).removeClass('ac_hidden');
                    }
                    if(data.responseJSON && data.responseJSON.html) {
                        $('#form-itoris-answer-'+idquest)[0].reset();
                        $('#itoris-newstler-add-'+idquest).hide();
                        $('#itoris_qa_add_answer-'+idquest).hide(500);
                        $('#itoris-div-button-show-'+idquest).show();
                        $('#itoris_div_conter_'+idquest+' .class-counter-question').text(0);
                        $('#itoris-qa-span'+idquest).hide();
                        $.ajax({
                            url: window.captchaUrlRefresh,
                            data: {formId: 'itoris_answer_captcha'},
                            type: "POST",
                            dataType: 'json',
                            showLoader: true,
                            complete: function (data) {
                                if(data.responseJSON && data.responseJSON.imgSrc){
                                    $('.itoris-container-accordion .form-answerqa .captcha-image img').prop('src',data.responseJSON.imgSrc)
                                }
                            }
                        });
                        $('#answer-ajax-question-answer_'+idquest).html(data.responseJSON.html);
                        countAnswer = countAnswer+1
                        $('.itoris-answer-count').html(countAnswer+1);
                        if(data.responseJSON.messagess){
                            QuestionInApprModal.html(data.responseJSON.messagess);
                            QuestionInApprModal.modal('openModal')
                        }
                    }else {
                       var text = data.responseText;
                        var messages='';
                        if(data.responseJSON && data.responseJSON.messagess){
                            messages=data.responseJSON.messagess;
                            QuestionInApprModal.html(messages);
                        }

                        $.ajax({
                            url: window.captchaUrlRefresh,
                            data: {formId: 'itoris_answer_captcha'},
                            type: "POST",
                            dataType: 'json',
                            showLoader: true,
                            complete: function (data) {
                                if(data.responseJSON && data.responseJSON.imgSrc){
                                    $('.itoris-container-accordion .form-answerqa .captcha-image img').prop('src',data.responseJSON.imgSrc)
                                }

                                if(messages){
                                    QuestionInApprModal.html(messages);
                                    QuestionInApprModal.modal('openModal');
                                    $('#form-itoris-answer-'+idquest)[0].reset();
                                    $('#itoris-newstler-add-'+idquest).hide();
                                    $('#itoris_qa_add_answer-'+idquest).hide(500);
                                    $('#itoris-div-button-show-'+idquest).show();
                                    $('#itoris_div_conter_'+idquest+' .class-counter-question').text(0);
                                    $('#itoris-qa-span'+idquest).hide();
                                }else {
                                    QuestionInApprModal.html(text);
                                    QuestionInApprModal.modal('openModal')
                                }

                            }
                        });
                    }

                }
            });
        }else{
            $('.itoris_producttabs_form').submit();
        }
    })
    $('.itoris-container-accordion').on('click','.a_info_block .icon_good',function(){
        var idquest = $(this).attr('data-answer-id');
        $.ajax({
            url: urlAnswerionRatingPlus,
            data: {id:idquest},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.url){
                    window.location.href = data.responseJSON.url;
                    return;
                }
                if(data.responseJSON && data.responseJSON.success){
                    $('#itoris-answer-span-good-'+idquest).text(data.responseJSON.count);
                }
            }
        });
    });
    $('.itoris-container-accordion').on('click','.a_info_block .icon_bad',function(){
        var idquest = $(this).attr('data-answer-id');
        $.ajax({
            url: urlAnswerRatingMinus,
            data: {id:idquest},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.url){
                    window.location.href = data.responseJSON.url;
                    return;
                }
                if(data.responseJSON && data.responseJSON.success){
                    $('#itoris-answer-span-bad-'+idquest).text(data.responseJSON.count);
                }
            }
        });
    });
    $('.itoris-container-accordion').on('click','.a_info_block .icon_inappr',function(){
        var idquest = $(this).attr('data-answer-id');
        $.ajax({
            url: urlAnswerInappr,
            data: {id:idquest},
            type: "POST",
            dataType: 'json',
            showLoader: true,
            complete: function (data) {
                if(data.responseJSON && data.responseJSON.url){
                    window.location.href = data.responseJSON.url;
                    return;
                }
                if(data.responseJSON && data.responseJSON.message){
                    QuestionInApprModal.html(data.responseJSON.message)
                    QuestionInApprModal.modal('openModal');
                }
            }
        });
    });
    $('.itoris-container-accordion').on('click','.q_info_block .button_add_answer .button_answer_question',function(){
        var idquest = $(this).attr('data-question-id');
        if(canRateUserA){
            window.location.href=canRateUserA;
            return;
        }
        $('#itoris_qa_add_answer-'+idquest).show(500);
        $('#itoris-div-button-show-'+idquest).hide();
        $('#itoris_qa_add_answer-'+idquest+' .captcha-reload').click();

        //$('#itoris_qa_add_answer-'+idquest)[0].animate({scrollIntoView: 0},500);
        $('html:not(:animated), body:not(:animated)').animate({
            scrollTop:  $('#itoris_qa_add_answer-'+idquest).offset().top
        }, 650);

        //$('#itoris_qa_add_answer-'+idquest)[0].scrollIntoView(250);


    });
    $('#productQaContainer').on('click','.itoris-newstler-checkbox',function(){
        var idNest=$(this).attr('data-question-id');
        if($(this).prop('checked')){
            $('#itoris-qa-span'+idNest).show();
            $('#itoris-newstler-add-'+idNest).show();
        }else{
            $('#itoris-qa-span'+idNest).hide();
            $('#itoris-newstler-add-'+idNest).hide();
        }

    });
    $('.itoris-container-accordion').on('click','.form-answerqa button.button.float_right.action.button_hide_form',function(){
        $(this).addClass('button_answer_question');
        var idquest = $(this).attr('data-question-id');
        $('#itoris-div-button-show-'+idquest).show();
        $('#form-itoris-answer-'+idquest)[0].reset();
        $('#itoris-newstler-add-'+idquest).hide();
        $('#itoris_qa_add_answer-'+idquest).hide(500);
        $('#itoris-qa-span'+idquest).hide();
        $('#itoris_div_conter_'+idquest+' .class-counter-question').text(0)

    });
    $('.itoris-container-accordion').on('click','#itoris_qa_pages li',function(){
        var paramsAcc = [];
        if(!(ItorisHelper.getQaHash())){
            ItorisHelper.updateHash('mode='+modeQa+'&page='+$(this).attr('data_page_id')+'&product_id='+productIdQ+'&pages='+pages+'&per_page='+perPage+'&store_id='+store_id_qa,'[m=]');
        }else {
            paramsAcc = ItorisHelper.getParamAccordion('[m=]','accordion')
            ItorisHelper.urlUpdate('page='+$(this).attr('data_page_id'),'[m=]','page');
        }
        $.ajax({
            url: ItorisHelper.getUrl('[m=]'),
            dataType: 'html',
            showLoader: true,
            complete: function (data) {
                var strSearch = false;
                strSearch = ItorisHelper.getParam('[m=]','s');
                if(strSearch){
                    $('#input-qa-search').val(strSearch);
                }
                var selectOrder = ItorisHelper.getParam('[m=]','mode');
                if(selectOrder){
                    $('#itoris_qa_select_menu option[value="'+selectOrder+'"]').prop('selected','selected')
                }
                $('.container-accordion').html(data.responseText);
                $('.itoris-container-accordion-div').show();
                $('#empty-question-qa-product').show();
                $('#itoris_qa_pages').show();
                if(paramsAcc && paramsAcc.length>1){
                    for(var ac=0;ac<paramsAcc.length; ac++){
                        $('#'+paramsAcc[ac]).removeClass('ac_hidden');
                    }
                }else {
                    $('#'+paramsAcc[0]).removeClass('ac_hidden');
                }
            }
        });
    });
});