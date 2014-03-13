/**
 * @copy 2013 Mageflow
 */

(function (mageflow, $, undefined) {

    /**
     * Returns data object with API credentials
     *
     * @returns {_L5.getCredentials.data}
     */
    mageflow.getCredentials = function () {
        var data = {
            consumer_key: jQuery('#mageflow_connect_api_consumer_key').val(),
            consumer_secret: jQuery('#mageflow_connect_api_consumer_secret').val(),
            token: jQuery('#mageflow_connect_api_token').val(),
            token_secret: jQuery('#mageflow_connect_api_token_secret').val(),
            api_url: jQuery('#mageflow_connect_advanced_api_url').val()
        };
        return data;
    };
    /**
     * Populates company select. After changing company select
     * another query to API is made to get list of this
     * company's projects
     *
     * @param {type} data
     */
    mageflow.populateCompanies = function (data) {
        jQuery(document).trigger(new jQuery.Event('disable_dynamic_fields'));
        jQuery('#mageflow_connect_api_company option[value!=""]').remove();
        jQuery(data.items).each(function (index, item) {
            var o = jQuery('<option>');
            o.attr('value', item.id).html(item.name);
            jQuery('#mageflow_connect_api_company').append(o);
        });
        jQuery('#mageflow_connect_api_company').removeClass('disabled').removeAttr('disabled').removeAttr('readonly');
    };
    /**
     * Populates list of projects. After selecting a project
     * a request is made to MF API to register current Magento developer
     * instance.
     *
     * @param {type} url
     */
    mageflow.getProjects = function (url, companyName) {
        var postData = mageflow.getCredentials();
//        console.log(url);
        postData.company_id = jQuery('#mageflow_connect_api_company').val();
        postData.company_name = companyName;
        console.log(postData);
        jQuery.ajax(url, {
            type: 'GET',
            data: postData,
            success: function (response) {
                jQuery('#mageflow_connect_api_project option[value!=""]').remove();
                jQuery(response.items).each(function (index, item) {
                    var o = jQuery('<option>');
                    o.attr('value', item.id).html(item.name);
                    jQuery('#mageflow_connect_api_project').append(o);
                });
                jQuery('#mageflow_connect_api_project').removeClass('disabled').removeAttr('disabled').removeAttr('readonly');
                jQuery('#mageflow_connect_api_project').data('register_query_url', response.register_query_url);
            }
        });
    };
    /**
     * Sends Magento Instance registration query to MF API
     * @param {type} url
     */
    mageflow.registerInstance = function (url) {
        var postData = mageflow.getCredentials();
        postData.mageflow_connect_api_company = jQuery('#mageflow_connect_api_company').val();
        postData.mageflow_connect_api_project = jQuery('#mageflow_connect_api_project').val();
        postData.mageflow_connect_api_instance_key = jQuery('#mageflow_connect_api_instance_key').val();
        console.log(jQuery('#mageflow_connect_api_project').val());
//        postData.project_name = mageflow_connect_api_project.options[jQuery('#mageflow_connect_api_project').val()].text;
//        postData.company_name = mageflow_connect_api_company.options[jQuery('#mageflow_connect_api_company').val()].text;
        console.log(postData);
        console.log(url);
        var parameters = {
            type: 'GET',
            data: postData,
            success: function (response) {
                console.log(response);
                jQuery('#mageflow_connect_api_instance_key').val(response.instance_key);
                jQuery('#btn_instance').closest('td').addClass('success-icon');
            }
        };
        jQuery.ajax(url, parameters);
    };
    /**
     * Create OAuth stuff in Magento
     * @param {type} url
     */
    jQuery(document).on('click', '#btn_oauth', function (event) {
        var el = jQuery(event.target).closest('button');
        var url = el.data('url');
        var postData = mageflow.getCredentials();
        postData.mageflow_connect_api_company = jQuery('#mageflow_connect_api_company').val();
        postData.mageflow_connect_api_project = jQuery('#mageflow_connect_api_project').val();
        postData.mageflow_connect_instance_key = jQuery('#mageflow_connect_api_instance_key').val();
//        console.log(url);
//        console.log('<instance key>');
//        console.log(jQuery('#mageflow_connect_api_instance_key').val());
//        console.log('</instance key>');
//        console.log(postData);
        jQuery.ajax(url, {
            type: 'GET',
            data: postData,
            success: function (response) {
//                console.log(response);
                jQuery('#btn_oauth').closest('td').addClass('success-icon');
            }
        });
    });

    /**
     *
     * @param {type} sender
     */
    mageflow.connect = function (sender) {
        var postData = mageflow.getCredentials();
        jQuery.ajax('/ajax.php', {
            type: 'POST',
            data: postData,
            success: function (response) {
                console.log(response);
            }
        });
    };
    /**
     * Class init method. Just a placeholder now.
     */
    mageflow.init = function () {

    };
    mageflow.init();
}(window.mageflow = window.mageflow || {}, jQuery));
/**
 * jQuery AJAX global event handlers
 */
jQuery(document).ajaxSend(function () {
    jQuery('#loading-mask').show();
});
jQuery(document).ajaxComplete(function () {
    jQuery('#loading-mask').hide();
});
/**
 * Event handlers
 */
jQuery(document).on('change', '#mageflow_connect_api_enabled', function (event) {
    var e = new jQuery.Event('validate_connect_button_status');
    e.sender = event.target;
    jQuery(document).trigger(e);
});
jQuery(document).on('blur', '.validate-connect', function (event) {
    var e = new jQuery.Event('validate_connect_button_status');
    e.sender = event.target;
    jQuery(document).trigger(e);
});
jQuery(document).on('validate_connect_button_status', function (event) {
    event.preventDefault();
    var status = 0;
    var apiEnabled = parseInt(jQuery('#mageflow_connect_api_enabled').val());
    jQuery(document).find('.form-list .validate-connect').each(function (index, item) {
        (jQuery(item).val().toString().length > 0) ? status++ : null;
    });
    if (status >= jQuery('.validate-connect').length && apiEnabled == 1) {
        jQuery('#btn_connect').removeClass('disabled');
    } else {
        jQuery('#btn_connect').addClass('disabled');
    }

});
jQuery(document).on('populate_company_select', function (event) {
    mageflow.populateCompanies(event.custom_data);
    jQuery(document).on('change', '#mageflow_connect_api_company', function (e) {
        mageflow.getProjects(event.custom_data.project_query_url, jQuery('#mageflow_connect_api_company option:selected').html());
    });
});

//jQuery(document).on('click', '#btn_instance', function(event) {
//    var register_query_url = jQuery(event.target).data('register_query_url');
//    console.log('');
//    mageflow.registerInstance(register_query_url);
//});

jQuery(document).on('disable_dynamic_fields', function (event) {
    jQuery('.mageflow-disabled-field').attr('disabled', 'disabled').attr('readonly', 'readonly');
});
jQuery(document).trigger(new jQuery.Event('disable_dynamic_fields'));
jQuery(document).trigger(new jQuery.Event('validate_connect_button_status'));

/*
$('select.action-select').each(function(index,item) {
    console.debug($(item).attr('onchange'));
});
*/
/*
$(document).on('click','select.action-select option', function(event) {
    //var hrefValue = $.parseJSON(event.target.value);
    //console.debug((hrefValue));
    alert('huu');
    //alert($(this).val());
});
*/

jQuery(document).ready(function(event) {
    jQuery('#migrationGrid_table').find('select.action-select').each(function(index, item){
        jQuery(item).attr('onchange','javascript:;');
    });

    jQuery('<input>').attr({
        type: 'hidden',
        id: 'migrationGrid_massaction-form-comment',
        name: 'comment',
        value: ''
    }).appendTo('#migrationGrid_massaction-form');
});

jQuery(document).on('change','select.action-select', function(event) {
    var hrefValue = event.target.value;
    var url = hrefValue.substr(hrefValue.search('http')).replace(/\\/g,'').replace('}','').replace('"','');
    if (url.search('push') > 0) {
        url += 'comment/' + prompt('Changeset description');
    }
    window.location = url;
});

jQuery(document).on('change','#migrationGrid_massaction-select', function(event) {
    var hrefValue = event.target.value;

    if (hrefValue == 'push') {
        var comment = prompt('Changeset description');
        jQuery('#migrationGrid_massaction-form-comment').val(comment);

    }
});
