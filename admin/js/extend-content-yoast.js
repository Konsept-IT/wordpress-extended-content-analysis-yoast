jQuery(document).ready(function($) {
    /*
    *   get URL parameter
    */
    function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (decodeURIComponent(sParameterName[0]) === sParam) {
                return typeof sParameterName[1] === 'undefined' || sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
        return null;
    };

    /*
    *   global vars
    */
    var postParameter   = getUrlParameter('post'),
        actionParameter = getUrlParameter('action');

    /*
    *   YOAST-SEO.JS
    *   https://github.com/Yoast/YoastSEO.js
    */

    // vars
    var extendContentAnalysis = function() {
        // register plugin
        YoastSEO.app.registerPlugin('extendContentAnalysis', {status: 'ready'});

        // get new content
        this.fetchData();
    };

    // get post content
    extendContentAnalysis.prototype.fetchData = function() {

        var _self = this;

        /* Return AJAX call to fetch your string content */
        return $.post({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'extend-content-yoast-get-post-content',
                postID: postParameter
            }
        })
        .done(function(response) {
            _self.extra_content = response.content;
            YoastSEO.app.pluginReady('extendContentAnalysis');

            /**
             * @param modification    {string}    The name of the filter
             * @param callable        {function}  The callable
             * @param pluginName      {string}    The plugin that is registering the modification.
             * @param priority        {number}    (optional) Used to specify the order in which the callables
             *                                    associated with a particular filter are called. Lower numbers
             *                                    correspond with earlier execution.
             */
            YoastSEO.app.registerModification('content', $.proxy(_self.getContent, _self), 'extendContentAnalysis', 5);
        })
        .fail(function(data) {
            console.error('extent-content-yoast: Unable to load post content data:', data);
        });

    };

    /**
    * Adds some text to the content
    *
    * @param content - The data to modify
    */
    extendContentAnalysis.prototype.getContent = function(content) {
        // plugin fetched content
        var currentContent  = content,
            // our new fetched content
            newContent      = this.extra_content;

        if (newContent){
            return newContent;
        }
        return currentContent;
    };

    /**
     * YoastSEO content analysis integration
    */
    if(postParameter && actionParameter == 'edit'){
        $(window).on('YoastSEO:ready', function() {
            new extendContentAnalysis();
        });
    }
});
