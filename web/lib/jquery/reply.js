$(document).ready(function () {
    $(".reply").click(function(e){
        e.preventDefault();
        var $form = $('.form-style-7');
        var $this = $(this);
        var parentId = $this.data('id');
        var $comment = $('#comment-' + parentId);
        $('#comment_parentId').val(parentId);
        $comment.after($form);
    });
});
