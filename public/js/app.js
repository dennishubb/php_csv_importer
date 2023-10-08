$(document).ready(function(){
    $("#uploadBtn").click(function(){
        $('#fileInput').click();
    });

    $('#fileInput').change(function() {
        var file = $(this).prop('files')[0];
        var form_data = new FormData();                  
        form_data.append('file', file);                           
        $.ajax({
            url: '/api/product/importCSV',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(response){
                console.log(response);
            }
        });

        $(this)[0].value = '';
    });

    doPoll();
});

function doPoll(){
    $.ajax({
        url: '/api/product/getImport',
        dataType: 'json',
        cache: false,
        data: {},                         
        type: 'post',
        success: function(response){
            console.log(response);
            var htmlString = ''
            response.data.forEach((item) => {
                var date = new Date(item.created_at);
                htmlString += "<tr><td>"+date.toLocaleString()+"</td><td>"+item.original_filename+"</td><td>"+item.status+"</td></tr>";
            });
            $("#importTable tbody").html(htmlString);
            setTimeout(doPoll,1000);
        }
    });
}