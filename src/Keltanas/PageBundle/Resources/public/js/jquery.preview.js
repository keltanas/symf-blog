/**
 * Preview plugin
 * @author: Nikolay Ermin <keltanas@gmail.com>
 */
(function($){
    $.fn.preview = function(){
        $(this).each(function(){
            $(this).on('click', function(event){
                var content = $('textarea.mark-down-preview').val(),
                    url = $(this).data('url'),
                    $modal = $('#modal-preview'),
                    $body = $modal.find('.modal-body');
                $modal.find('.modal-body').html('<p>loading</p>').end().modal('show');
                console.log(content);
                $.post(url, {content: content}, function(text){
                    $body.html(text);
                    hljs.highlightBlock($body[0]);
                })
            });
        });
    };

    $(function(){
        $('.btn-preview').preview();
    });
})(jQuery);
