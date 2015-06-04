/**
 * Form - javascript
 *
 * @package Boots
 * @subpackage Form
 * @version 1.0.2
 * @license GPLv2
 *
 * Boots - The missing WordPress framework. http://wpboots.com
 *
 * Copyright (C) <2014>  <M. Kamal Khan>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */
(function($){
    "use strict";

    var BootsFormObj = {

        uploader_path : boots_form.uploader_path,
        action_image_fetch : boots_form.action_image_fetch,
        nonce_image_fetch : boots_form.nonce_image_fetch,

        init : function(options, elem)
        {
            var self = this;
            self.elem = elem;
            self.$elem = $(elem);
            self.options = $.extend({}, $.fn.BootsForm.options, options);

            // method calls
            self.iris();
            self.select2();
            self.switchery();
            self.nouislider();
            self.image_handler();
			self.tagger();
            self.uploader();
            self.$elem.trigger('boots-form:init', self);
        },

        // enable iris color picker
        // uses $.fn.wpColorPicker()
        iris : function()
        {
            var self = this;

            $('.iris', self.$elem).wpColorPicker({
                target: true
            });
        },

        // enable select2
        // uses $.fn.select2()
        select2 : function()
        {
            var self = this;

            $('select', self.$elem).select2({
                width: 'element'
            });
        },

        // enable switchery
        // uses Switchery()
        switchery : function()
        {
            var self = this;

            $('input[type="checkbox"]', self.$elem).each(function (i, el){
                var switchery = new Switchery(el, {
                    color          : $(this).data('color') || '#64bd63'
                  , secondaryColor : $(this).data('secondarycolor') || '#d7d7d7'
                  , jackColor      : $(this).data('jackcolor') || '#fff'
                  , className      : $(this).data('classname') || 'switchery'
                  , disabled       : $(this).data('disabled') || false
                  , disabledOpacity: $(this).data('disabledopacity') || 0.5
                  , speed          : $(this).data('speed') || '0.5s'
                  , size           : $(this).data('size') || 'small'
                });
            });
        },

        // enable nouislider
        // uses $.fn.noUiSlider()
        nouislider : function()
        {
            var self = this;

            var $range, $target, $args, args;
            $('.boots-form-nouislider', self.$elem).each(function (i, el){
                $range = $('.boots-form-nouislider-range', $(this));
                $target = $('input', $(this));
                $range.noUiSlider({
                    start: $target.data('start') || $target.data('min') || 0,
                    range: {
                        min: $target.data('min') || 0,
                        max: $target.data('max') || 100
                    },
                    serialization: {
                        format: {decimals : $target.data('decimals') || 0},
                        lower: [$.Link({target : $target})]
                    }
                });
            });
        },

		// tagger
        tagger : function()
        {
            var self = this;

            $('.boots-form-tagger', self.$elem).select2({
                tags  : '',
                width : '100%'
            });
        },

        // uploader
        uploader : function()
        {
            var self = this;
            Dropzone.autoDiscover = false;
            $('.boots-form-uploader', self.$elem).each(function (i, el){
                var $el = $(this);
                $el.css('height', 'auto', 'important');
                var myDropzone = new Dropzone($el[0], {
                    url: self.uploader_path,
                    addRemoveLinks : true,
                    thumbnailWidth : 80,
                    thumbnailHeight : 80,
                    //acceptedFiles : '.pdf',
                    dictDefaultMessage : 'Drop files',
                    dictRemoveFile: 'X'/*,
                    resize : function (file){
                        var xhr = file.xhr;
                        //F.file;
                        console.log(file);
                        return {
                            srcX: 0,
                            srcY: 0,
                            srcWidth: 80,
                            srcHeight: 80
                        }
                    }*/
                });
                myDropzone.on('addedfile', function (file){
                    $el.on('mouseover', function (e){
                        $('.dz-remove', $(this)).show();
                    }).on('mouseout', function (e){
                        $('.dz-remove', $(this)).hide();
                    });
                    $el.AwesomeGrid({
                        rowSpacing  : 10,
                        colSpacing  : 10,
                        initSpacing : 0,
                        responsive  : true,
                        fadeIn      : true,
                        hiddenClass : false,
                        item        : '.dz-preview',
                        columns     : {
                            'defaults' : 7,
                            '700'      : 5,
                            '350'      : 3,
                            '100'      : 1
                        },
                        context     : 'self',
                        onReady     : function($item){
                            $item.stop(false, false);
                            $('.dz-message', $el).css('position', 'absolute');
                            $el.css('height', $el.height() + $item.height() - 20, 'important');
                        }
                    });
                });
            });
        },

        // image handler
        // uses $.fn.BootsMedia()
        image_handler : function()
        {
            var self = this;

            $('.boots-form-img-upload input').each(function(i){
                var $parent = $(this).parent();
                if(($(this).val()).trim() != '')
                {
                    self.fetch_image($parent, $(this).val(), $parent.width(), 73);
                }
            });

            $('.boots-form-img-upload button', self.$elem).BootsMedia('click', {
                multiple : false,
                done : function(attachment, $button){
                    var $parent = $button.parent();
                    $('input', $parent).val(attachment.url);
                    $('a.boots-form-img-cross', $parent).show();
                    $button.hide();
                    $parent.css({
                        'height'  : '73px',
                        'padding' : '0'
                    });
                    self.fetch_image($parent, attachment.id, $parent.width(), 73);
                }
            });

            $('.boots-form-img-upload a.boots-form-img-cross', self.$elem).on('click', function(){
                var $a = $(this);
                var $parent = $a.parent();
                var $btn = $('button', $parent);
                var $input = $('input', $parent);
                var $img = $('img', $parent);
                $input.val('');
                $img.slideUp('fast', function(){
                    $parent.css({
                        'height'  : 'auto',
                        'padding' : '21px'
                    });
                    $img.remove();
                    $a.fadeOut();
                    $btn.slideDown('fast');
                });
                return false;
            });
        },

        // fetch image ajax
        // uses $(document).BootsAjax()
        fetch_image : function($elem, id, width, height)
        {
            var self = this;

            $.BootsAjax({
                data : {
                    id: id,
                    width: parseInt(width),
                    height: parseInt(height)
                },
                action : self.action_image_fetch,
                nonce : self.nonce_image_fetch,
                done : function(Data){
                    if(!Data.error)
                    {
                        $elem.append('<img src="'+Data.url+'" style="max-width:100%;height:auto;" />');
                        //$('img', $elem).css('margin-top', $elem.height()/2 - $('img', $elem).height()/2);
                    }
                }
            });
        },

        showElement : function($e)
        {
            $e.slideDown('fast', function(){
                $(this).trigger($.Event('resize'));
            });
        },
        hideElement : function($e)
        {
            $e.slideUp('fast', function(){
                $(this).trigger($.Event('resize'));
            });
        },
        conditionalElement : function($wrapper, conds, ne)
        {
            var self = this;
            var $_el1, $_el2;
            for(var i = 0; i < conds.length; i++)
            {
                $_el1 = $('[name="' + conds[i].el + '"]');
                if(!ne)
                    $_el1.on('keyup change', function (){
                        self.conditionalElement($wrapper, conds, true);
                    });
                if(['checkbox','radio'].indexOf($_el1.prop('type')) > -1) {
                    $_el2 = $('[name="' + conds[i].el + '"]:checked');
                    if(conds[i].val.indexOf($_el2.val()) < 0)
                        return this.hideElement($wrapper);
                } else if(conds[i].val.indexOf($_el1.val()) < 0) {
                    return this.hideElement($wrapper);
                }
            }
            return this.showElement($wrapper);
        }
    };

    $.fn.BootsForm = function(options) {
        return this.each(function(){
            var Obj = function(){
                function F(){};
                F.prototype = BootsFormObj;
                return new F();
            }();
            Obj.init(options, this);
        });
    };

    $.fn.BootsForm.options = {

    };

})(jQuery);

jQuery(document).ready(function($){
    $('.boots-form').BootsForm({

    });
});
