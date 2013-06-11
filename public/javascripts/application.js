content_search = {};

jQuery(function ($) {
    STUDIP.ContentSearchDialog = {
        dialog: null,
        initialize: function (url) {
            if (STUDIP.ContentSearchDialog.dialog === null) {
                $.ajax({
                    url: url,
                    data: {},
                    success: function (data) {
                        STUDIP.ContentSearchDialog.dialog =
                            jQuery('<div id="ContentSearchDialogBox">' + data.content + '</div>').dialog({
                                show: '',
                                hide: 'scale',
                                title: data.title,
                                draggable: true,
                                modal: true,
                                resizable: false,
                                width: Math.min(800, $(window).width() - 64),
                                height: 'auto',
                                maxHeight: $(window).height(),
                                close: function () {
                                    $(this).remove();
                                    STUDIP.ContentSearchDialog.dialog = null;
                                }
                            });
                            $('#ContentSearchDialogBox').zIndex(1002);
                    }
                });
            }
        }
    };
    $('input[name*="search_only[ext]"]').click(function () {
                var clicked_id = this.id;
                if (clicked_id === 'search_only[ext][all]') {
                    $('input[name*="search_only[ext]"]').attr('checked', function (i) {
                        return i === 0;
                    });
                } else {
                    $('input[name="search_only[ext][all]"]').attr('checked', false);
                }
            });
});
