$(document).ready(function () {
    $('#starts').click(function (e) {


        // $.get('/ajax', function(data){
        //     console.log(data);
        // });
        //
        // e.preventDefault();

        // var $token = $('meta[name="csrf-token"]').attr('content');
        // var $action = $('#action').val();
        //
        // $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $token }});
        // $.ajax({
        //     url: '/home',
        //     type: 'POST',
        //     data: {'action': $action, '_token': $token, '_method': 'post'},
        //     dataType: 'json',
        //     success: function ($response) {
        //        //$('#parser').css('display', 'block');
        //         alert('ok');
        //        console.log($response);
        //    },
        //    error: function () {
        //        console.log('error');
        //    }
        // });

       //return false;
    });

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $("#start").click(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            /* the route pointing to the post function */
            url: '/home',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, _method: 'post', message: 'ok'},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
                //$('#parser').css('display', 'block');
                //alert(xhr.getResponseHeader('Status Code'));
                console.log(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //alert(jqXHR.status);
                if (jqXHR.status = 200) {
                    console.log('ok');
                }
            },
            complete: function() {
                location.href = "/home?st=f";
            }
        });
        $('#parser').css('display', 'block');
        return false;
    });
});
