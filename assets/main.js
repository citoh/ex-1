$(window).on("load",function() {
    loadArticlesTabe();
});

function loadArticlesTabe(){
    $.ajax({
        type: "post",
        url: "app/endpoint_a?order=author_name&sort=asc",
        beforeSend: function( xhr ) {
            xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
        },
        dataType:"json",
        success: function (resp) {
            console.log(resp.data);
            $.each(resp.data, function(i,item){
                var row = 
                    '<div class="row">'+
                        '<div class="cell"><input type="checkbox"></div>'+
                        '<div class="cell">' + item.article_id + '</div>'+
                        '<div class="cell">' + item.article_title + '</div>'+
                        '<div class="cell">' + item.author_name + '</div>'+
                        '<div class="cell">' + item.country + '</div>'+
                    '</div>';
                $('#table-articles').append(row);
            });
            
        }
    });
}