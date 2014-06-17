<?php

/**
 * Form
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

class Boots_Form
{
    private $Boots;
    private $Settings;
    private $dir;
    private $url;

    private $Cache = array();

    public function __construct($Boots, $Args, $dir, $url)
    {
        $this->Boots = $Boots;
        $this->Settings = $Args;
        $this->dir = $dir;
        $this->url = $url;

        if(!has_action('boots_ajax_boots_form_image_fetch', array(&$this, 'ajax_image_fetch')))
        {
            add_action('boots_ajax_boots_form_image_fetch', array(&$this, 'ajax_image_fetch'));
        }
    }

    // call this when you need
    // to enqueue the styles for your form
    // (Usually on the relevant pages)
    public function styles()
    {
        $this->Boots->Enqueue
        ->style('wp-color-picker')->done()
        ->raw_style('select2')
            ->source($this->url . '/third-party/select2-3.4.5/select2.css')
            ->done()
        ->raw_style('boots_form')
            ->source($this->url . '/css/boots_form.css')
            ->requires('wp-color-picker')
            ->requires('select2')
            ->done();
    }

    // call this when you need
    // to enqueue the scripts for your form
    // (Usually on the relevant pages)
    public function scripts()
    {
        $this->Boots->Ajax->scripts();
        $this->Boots->Media->scripts();

        $this->Boots->Enqueue
        ->script('jquery')->done()
        ->script('wp-color-picker')->done()
        ->raw_script('select2')
            ->source($this->url . '/third-party/select2-3.4.5/select2.min.js')
            ->requires('jquery')
            ->done(true)
        ->raw_script('boots_form')
            ->source($this->url . '/js/boots_form.min.js')
            ->requires('wp-color-picker')
            ->requires('select2')
            ->requires('boots_media')
            ->requires('boots_ajax')
            ->vars('action_image_fetch', 'boots_form_image_fetch')
            ->vars('nonce_image_fetch', wp_create_nonce('boots_form_image_fetch'))
            ->done(true);
    }

    private function cache($what, $term, $value)
    {
        $this->Cache[$what][$term] = $value;
    }

    private function cached($what, $term)
    {
        if(isset($this->Cache[$what]) && isset($this->Cache[$what][$term]))
        {
            return $this->Cache[$what][$term];
        }
        else
        {
            return false;
        }
    }

    private function extract_args($Args)
    {
        $Array = array_merge(array(
            'title' => false,
            'name' => false,
            'id' => false,
            'help' => false,
            'class' => false,
            'style' => false,
            'data' => array(),
            'value' => null
        ), $Args);
        $Array['id'] = !$Array['id'] ? $Array['name'] : $Array['id'];
        return $Array;
    }

    private function value($term, $value = null)
    {
        return !is_null($value)
        ? $value
        : $this->Boots->Database->term($term, false)->get();
    }

    private function get_label_tag($label, $id, $style = false)
    {
        $style = $style ? $this->get_style_attr($style) : '';
        return $label
               ? ('<label' . ($id ? (' for="' . $id . '"') : '') . $style . '>' . $label . '</label>')
               : '';
    }

    private function get_help_tag($help)
    {
        return $help ? ('<p>' . $help . '</p>') : '';
    }

    private function get_name_attr($name)
    {
        return $name ? (' name="' . $name . '"') : '';
    }

    private function get_id_attr($id)
    {
        return $id ? (' id="' . $id . '"') : '';
    }

    private function get_class_attr($class)
    {
        return $class ? (' class="' . $class . '"') : '';
    }

    private function get_style_attr($style)
    {
        return $style ? (' style="' . $style . '"') : '';
    }

    private function get_attributes($name, $id, $class, $style)
    {
        return $this->get_name_attr($name)
             . $this->get_id_attr($id)
             . $this->get_class_attr($class)
             . $this->get_style_attr($style);
    }

    private function generate_custom($type, $Args)
    {
        $Args = $this->extract_args($Args);
        $label_tag = $this->get_label_tag($Args['title'], $Args['id']);
        $attrs = $this->get_attributes($Args['name'], $Args['id'], $Args['class'], $Args['style']);
        $value = $this->value($Args['name']);
        $help_tag = $this->get_help_tag($Args['help']);
        return apply_filters('boots_form_field_' . $type, '', $Args, $value, $label_tag, $attrs, $help_tag);
    }

    private function generate_html($Args)
    {
        return $Args;
    }

    private function generate_textbox($Args, $flavour = 'text')
    {
        $html = '';

        extract($this->extract_args($Args));

        if($flavour != 'hidden')
        {
            $html .= $this->get_label_tag($title, $id);
            $html .= '<div class="boots-form-input">';
        }

        $html .= '<input type="' . $flavour . '"';
        $html .= $this->get_attributes($name, $id, $class, $style);
        $html .= 'value="' . $this->value($name, $value) . '"';
        $html .= ' />';

        if($flavour != 'hidden')
        {
            $html .= '</div>';
            $html .= $this->get_help_tag($help);
        }


        return $html;
    }

    private function generate_textarea($Args)
    {
        $html = '';

        extract($this->extract_args($Args));

        $html .= $this->get_label_tag($title, $id);

        $html .= '<div class="boots-form-input">';

        $html .= '<textarea';
        $html .= $this->get_attributes($name, $id, $class, $style);
        $html .= '>' . $this->value($name, $value) . '</textarea>';

        $html .= '</div>';

        $html .= $this->get_help_tag($help);

        return $html;
    }

    private function generate_color_picker($Args)
    {
        $Args['class'] = array_key_exists('class', $Args)
                       ? ($Args['class'] . ' ')
                       : '';
        $Args['class'] .= 'iris';
        return $this->generate_textbox($Args);
    }

    private function generate_select($Args, $multi = false)
    {
        $html = '';

        extract($this->extract_args($Args));

        $html .= $this->get_label_tag($title, $id);

        $html .= '<div class="boots-form-input">';

        $html .= '<select';
        $html .= $this->get_attributes($name . ($multi ? '[]' : ''), $id, $class, $style);
        $html .= $multi ? ' multiple="multiple"' : '';
        $html .= '>';
        foreach($data as $i => $v)
        {
            $html .= '<option value="'. $i .'"';
            if(!$multi)
            {
                $html .= ($i === $this->value($name, $value)) ? ' selected="selected"' : '';
            }
            else
            {
                $Mv = $this->value($name, $value);
                $html .= (in_array($i, is_array($Mv) ? $Mv : array())) ? ' selected="selected"' : '';
            }
            $html .= '>' . $v . '</option>';
        }
        $html .= '</select>';

        $html .= '</div>';

        $html .= $this->get_help_tag($help);

        return $html;
    }

    private function generate_checkboxes($Args)
    {
        $html = '';

        extract($this->extract_args($Args));
        $h = $help;

        $html .= '<div class="boots-form-input">';

        $i = 1;
        $it = count($data);
        foreach($data as $t => $Arr)
        {
            $checked = false;
            $val = null;
            extract($this->extract_args($Arr));
            $inner = '<input type="checkbox"';
            $inner .= $this->get_attributes($name, $id, $class, $style);
            $inner .= $checked || ($val == $this->value($name, $value))
                    ? ' checked="checked"'
                    : '';
            $inner .= ' value="' . $val . '" /> ';
            $css_style = ($i++ == $it) ? 'margin-bottom: 0;' : false;
            $html .= $this->get_label_tag($inner . $t, $id, $css_style);
        }

        $html .= '</div>';

        $html .= $this->get_help_tag($h);

        return $html;
    }

    private function generate_radio($Args)
    {
        $html = '';

        extract($this->extract_args($Args));
        $h = $help;
        $n = $name;

        $html .= '<div class="boots-form-input">';

        $i = 1;
        $it = count($data);
        foreach($data as $t => $Arr)
        {
            $checked = false;
            $val = null;
            extract($this->extract_args($Arr));
            $inner = '<input type="radio"';
            $inner .= $this->get_attributes($n, $id, $class, $style);
            $inner .= $checked || ($val == $this->value($n, $value))
                    ? ' checked="checked"'
                    : '';
            $inner .= ' value="' . $val . '" /> ';
            $css_style = ($i++ == $it) ? 'margin-bottom: 0;' : false;
            $html .= $this->get_label_tag($inner . $t, $id, $css_style);
        }

        $html .= '</div>';

        $html .= $this->get_help_tag($h);

        return $html;
    }

    private function generate_image_uploader($Args)
    {
        $html = '';

        extract($this->extract_args($Args));

        $img = $this->value($name, $value);

        $html .= $this->get_label_tag($title, $id);

        $html .= '<div class="boots-form-input">';

        $html .= '
        <div class="boots-form-img-upload">
            <button data-uploader_title="' . $title . '" data-uploader_button_text="' . (isset($button) ? $button : 'Choose Image') . '" data-for="' . $id . '"';
        $html .= $img ? ' style="display: none;"' : '';
        $html .= '>' . (isset($button) ? $button : 'Choose Image');
        $html .= '</button>';
        $html .= $img ? '<img src="' . $img . '" width="100%" />' : '';
        $html .= '<a href="#" class="boots-form-img-cross" title="Remove"';
        $html .= !$img ? ' style="display: none;"' : '';
        $html .= '></a>';
        $html .= '<input type="hidden"';
        $html .= $this->get_attributes($name, $id, $class, $style);
        $html .= ' value="' . $img . '"';
        $html .= ' />
        </div>';

        $html .= '</div>';

        $html .= $this->get_help_tag($help);

        return $html;
    }

    private function generate_tinymce($Args)
    {
        extract($this->extract_args($Args));

        echo $this->get_label_tag($title, $id);

        $name = $name ? $name : $id;

        echo '<div class="boots-form-input boots-form-input-wide">';

        wp_editor($this->value($name, $value), $id, array(
            'textarea_name' => $name,
            'textarea_rows' => isset($rows) ? $rows : 10,
            'wpautop' => isset($wpautop) ? $wpautop : true,
            'media_buttons' => isset($media) ? $media : true,
            'editor_css' => $style ? $style : '',
            'editor_class' => $class ? $class : '',
            'teeny' => isset($teeny) ? $teeny : false,
        ));

        echo '</div>';

        echo $this->get_help_tag($help);
    }

    private function generate_posts($Args, $for = 'post')
    {
        $Query = array_merge(array(
            'post_type' => $for,
            'posts_per_page' => -1
        ), array_key_exists('query', $Args) ? $Args['query'] : array());

        $slug = array_key_exists('slug', $Args) ? $Args['slug'] : 'global';
        $Posts = $this->cached($for, $slug);
        if($Posts === false)
        {
            $Posts = array();
            global $wpdb;
            $P = array();
            $P_Q = new WP_Query($Query);
            while ($P_Q->have_posts()) : $P_Q->the_post();
                $Posts[get_the_ID()] = get_the_title();
            endwhile;
            wp_reset_postdata();
            $this->cache($for, $slug, $Posts);
        }
        $Args['data'] = array_merge($Posts, array_key_exists('data', $Args) ? $Args['data'] : array());
        krsort($Args['data']);
        return $this->generate_select($Args, array_key_exists('multiple', $Args) ? $Args['multiple'] : false);
    }

    // To view the args accepted for the $Args['query']
    // visit http://codex.wordpress.org/Function_Reference/get_categories
    private function generate_taxonomy($Args, $taxonomy = 'category')
    {
        $Query = array_merge(array(
            'type'                     => 'post',
            'child_of'                 => 0,
            'parent'                   => '',
            'orderby'                  => 'name',
            'order'                    => 'ASC',
            'hide_empty'               => 0,
            'hierarchical'             => 1,
            'exclude'                  => '',
            'include'                  => '',
            'number'                   => '',
            'taxonomy'                 => $taxonomy,
            'pad_counts'               => false

        ), array_key_exists('query', $Args) ? $Args['query'] : array());

        $slug = array_key_exists('slug', $Args) ? $Args['slug'] : 'global';
        $Categories = $this->cached($taxonomy, $slug);
        if($Categories === false)
        {
            $Categories = array();
            $C = get_categories($Query);
            foreach($C as $Cat)
            {
                $Categories[$Cat->term_id] = $Cat->name;
            }
            $this->cache($taxonomy, $slug, $Categories);
        }
        $Args['data'] = array_merge($Categories, array_key_exists('data', $Args) ? $Args['data'] : array());
        krsort($Args['data']);
        return $this->generate_select($Args, array_key_exists('multiple', $Args) ? $Args['multiple'] : false);
    }

    // To view the args accepted for the $Args['query']
    // visit http://codex.wordpress.org/Function_Reference/get_tags
    private function generate_tags($Args)
    {
        $Query = array_merge(array(
            'hide_empty ' => 0

        ), array_key_exists('query', $Args) ? $Args['query'] : array());

        $slug = array_key_exists('slug', $Args) ? $Args['slug'] : 'global';
        $Tags = $this->cached('tags', $slug);
        if($Tags === false)
        {
            $Tags = array();
            $T = get_tags($Query);
            foreach($T as $Tag)
            {
                $Tags[$Tag->term_id] = $Tag->name;
            }
            $this->cache('tags', $slug, $Tags);
        }
        $Args['data'] = array_merge($Tags, array_key_exists('data', $Args) ? $Args['data'] : array());
        krsort($Args['data']);
        return $this->generate_select($Args, array_key_exists('multiple', $Args) ? $Args['multiple'] : false);
    }

    public function generate($type, $Args)
    {
        switch($type)
        {
            case 'html':
                return $this->generate_html($Args);
            break;
            case 'textbox':
                return $this->generate_textbox($Args);
            break;
            case 'password':
                return $this->generate_textbox($Args, 'password');
            break;
            case 'email':
                return $this->generate_textbox($Args, 'email');
            break;
            case 'number':
                return $this->generate_textbox($Args, 'number');
            break;
            case 'search':
                return $this->generate_textbox($Args, 'search');
            break;
            case 'tel':
                return $this->generate_textbox($Args, 'tel');
            break;
            case 'url':
                return $this->generate_textbox($Args, 'url');
            break;
            case 'file':
                return $this->generate_textbox($Args, 'file');
            break;
            case 'hidden':
                return $this->generate_textbox($Args, 'hidden');
            break;
            case 'textarea':
                return $this->generate_textarea($Args);
            break;
            case 'color':
                return $this->generate_color_picker($Args);
            break;
            case 'select':
                return $this->generate_select($Args);
            break;
            case 'multiple':
                return $this->generate_select($Args, true);
            break;
            case 'checkboxes':
                return $this->generate_checkboxes($Args);
            break;
             case 'radio':
                return $this->generate_radio($Args);
            break;
            case 'image':
                return $this->generate_image_uploader($Args);
            break;
            case 'tinymce':
                return $this->generate_tinymce($Args);
            break;
            case 'posts':
                return $this->generate_posts($Args, 'post');
            break;
            case 'pages':
                return $this->generate_posts($Args, 'page');
            break;
            case 'categories':
                return $this->generate_taxonomy($Args, 'category');
            break;
            case 'tags':
                return $this->generate_tags($Args);
            break;
            default:
                return $this->generate_custom($type, $Args);
            break;
        }
    }

    // uses $this->Boots->Media
    public function ajax_image_fetch($nonce)
    {
        header('content-type: application/json; charset=utf-8');
        // check for $nonce first
        if(!wp_verify_nonce($nonce, 'boots_form_image_fetch'))
        {
            die(json_encode(array('error'=>'insecure access')));
        }
        // good to go

        if(!array_key_exists('id', $_POST) || !is_numeric($_POST['id']))
        {
            die(json_encode(array('error'=>'invalid image id')));
        }

        $id = esc_attr($_POST['id']);
        $width = esc_attr($_POST['width']);
        $height = esc_attr($_POST['height']);

        // Response['url'] Response['width'] Response['height']
        $Response = $this->Boots->Media
                    ->image($id)
                        ->width($width)
                        ->height($height)
                        ->get(true); // array

        // return response
        die(json_encode($Response));
    }
}