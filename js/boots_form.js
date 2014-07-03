/**
 * Form - javascript
 *
 * @package Boots
 * @subpackage Form
 * @version 1.0.0
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

        action_image_fetch : boots_form.action_image_fetch,
        nonce_image_fetch : boots_form.nonce_image_fetch,

        init : function(options, elem)
        {
            var self = this;
            self.elem = elem;
            self.$elem = $(elem);
            self.options = $.extend({}, $.fn.BootsForm.options, options);

            // method calls
            self.select2();
            self.iris();
            self.image_handler();
            self.nouislider();
        },

        // enable iris color picker
        // uses $.fn.wpColorPicker()
        iris : function()
        {
            var self = this;

            $('.iris', self.$elem).wpColorPicker({
                // a callback to fire whenever the color changes to a valid color
                change: function(event, ui){
                    // event = standard jQuery event, produced by whichever control was changed.
                    // ui = standard jQuery UI object, with a color member containing a Color.js object
                    $(this).css('background-color', ui.color.toString()).attr('value', ui.color.toString());
                },
                // a callback to fire when the input is emptied or an invalid color
                clear: function() {},
                // hide the color picker controls on load
                hide: true,
                // show a group of common colors beneath the square
                // or, supply an array of colors to customize further
                palettes: true
            });
            var $wrap;
            $('.iris', self.$elem).each(function(){
                $wrap = $(this).parent().parent().parent();
                $(this).iris('option', 'width', $wrap.width() - 10);
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

        // enable nouislider
        // uses $.fn.noUiSlider()
        nouislider : function()
        {
            var self = this;

            $('.boots-form-nouislider', self.$elem).each(function(i){
                var $range = $('.boots-form-nouislider-range', $(this));
                var $target = $('> input', $(this));
                var $args = $('.boots-form-nouislider-args', $(this));
                var args = $.parseJSON(($args.html()).trim());
                $range.noUiSlider($.extend({start: 50, range: {'min': 0,'max': 100},
                    serialization: {
                        format: {decimals : 0},
                        lower: [$.Link({target : $target})]
                    }
                }, args));
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
                        $elem.append('<img src="'+Data.url+'" width="'+Data.width+'" height="'+Data.height+'" />');
                        $('img', $elem).css('margin-top', $elem.height()/2 - $('img', $elem).height()/2);
                    }
                }
            });
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