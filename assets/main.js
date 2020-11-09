var base_url = "appPHP";
//var base_url  = "http://localhost:3000"

var count     = 0;
var firstItem = 0;
var lastItem  = 0;
var page      = -1;
var perPage   = -1;
var order     = "author_name";
var sort      = "asc";


$(window).on("load",function() {
    loadArticlesTabe(page, perPage, order, sort);
    
    $("#btnAddArticle").click(function(){
        var title = encodeURI(  $('#title').val()  );
        var author = encodeURI( $('#author').val() );
        addArticle(title, author);
        $('#title').val('');
        $('#author').val('');
    });

    $("#btnRemoveArticles").click(function(){
        var ids = []
        $('.cb-article').each(function(){
            if( $(this).prop('checked') )
                ids.push( $(this).attr('data-id') );                
        });
        removeArticles(ids);
    });

    $('#check-box-all').change(function(){
        $('input:checkbox.cb-article').not(this).prop('checked', this.checked);
    });

    $('#author').on('keypress',function(e) {
        if(e.which == 13) {
            var title = encodeURI(  $('#title').val()  );
            var author = encodeURI( $('#author').val() );
            if(title != '' && author != ''){
                addArticle(title, author);
                $('#title').val('');
                $('#author').val('');
                $('#title').focus();
            }
        }
    });

});



function loadArticlesTabe(page, perPage, order, sort){
    console.log(base_url+"/endpoint_a?page="+page+"&per_page="+perPage+"&order="+order+"&sort="+sort);
    $.ajax({
        type: "get",
        data: { 
            page     : page,
            per_page : perPage,
            order    : order,
            sort     : sort
        },
        url: base_url+"/endpoint_a",
        dataType:"json",
        success: function (resp) {
            console.log(resp);
            console.log(resp.data);
            listArticles(resp.data);
        }
    });
}

function listArticles(data){
    $(".row.item").remove();
    $.each(data, function(i,item){
        var row = 
            '<div class="row item">'+
                '<div class="cell"><input type="checkbox" class="cb-article" data-id="'+item.article_id+'"></div>'+
                '<div class="cell">' + item.article_id + '</div>'+
                '<div class="cell">' + item.article_title + '</div>'+
                '<div class="cell">' + item.author_name + '</div>'+
                '<div class="cell">' + item.country_name + '</div>'+
            '</div>';
        $('#table-articles').append(row);
    });
}

function addArticle(title, author){
    $.ajax({
        type: "get",
        data: { 
            title    : title,
            author   : author,
            page     : page,
            per_page : perPage,
            order    : order,
            sort     : sort
        },
        url: base_url+"/endpoint_b",
        dataType:"json",
        success: function (resp) {
            console.log(resp.data);
            listArticles(resp.data);
        }
    });
}

function removeArticles(ids){
    $.ajax({
        type: "get",
        data: { 
            ids      : ids,
            page     : page,
            per_page : perPage,
            order    : order,
            sort     : sort
        },
        url: base_url+"/endpoint_d",
        dataType:"json",
        success: function (resp) {
            console.log(resp.data);
            listArticles(resp.data);
        }
    }).done(
        
    );
}