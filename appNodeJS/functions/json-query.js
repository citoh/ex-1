var fs = require('fs');

const _ARTICLES_PATH  = '../data/articles.json'
const _AUTHORS_PATH   = '../data/authors.json'
const _COUNTRIES_PATH = '../data/countries.json'

const articles  = JSON.parse( fs.readFileSync( _ARTICLES_PATH  ) )
const authors   = JSON.parse( fs.readFileSync( _AUTHORS_PATH   ) )
const countries = JSON.parse( fs.readFileSync( _COUNTRIES_PATH ) )

function saveJson(data, file){
    let json = JSON.stringify(data, null, 2);
    fs.writeFile(file, json, (err) => {
        if (err) throw err;
        console.log(file+' has been saved.');
    });
}

function getAuthorById( id ){
    for(let i in authors){
        if(authors[i].id === id ){
            return authors[i]
            break;
        }
    }
    return [];
}

function getCountryByCode( code ){
    if(typeof code === 'undefined' || code === '' )
        return ''
    return countries[code.toLowerCase()];
}

module.exports = class jsonQuery{

    data = {}

    constructor(){ 
        this.query()
    }

    query = function(page = -1, perPage = -1, order = "author_name", sort="asc"){
        var allData = this.getAllData()
        allData.sort(
            function(a, b) {
                if(sort === "desc")
                    return a[order] < b[order] ? 1 : -1;
                return a[order] > b[order] ? 1 : -1;
            }); 

        var count = allData.length
        var firstItem = 0
        var lastItem  = count - 1

        if(page !== -1 && perPage !== -1){
            var firstItem = (page -  1) * perPage
            var lastItem  = page * perPage
            lastItem = (lastItem > count)? count : lastItem;
            
            if(firstItem >= count){
                firstItem = 0;
                lastItem  = 0;
            }
        }

        var data = allData.slice(firstItem, lastItem);
        
        return {
            data      : data,
            vars      : {
                count     : count,
                perPage   : parseInt(perPage),
                page      : parseInt(page),
                firstItem : firstItem,
                lastItem  : lastItem,
                order     : order,
                sort      : sort
            }
        };
    }

    getAllData = function(){
        var data = []
    
        articles.forEach(function(article){
            
            let author  = getAuthorById(article.author_id)
            let country = getCountryByCode(author.country_code)  
    
            data.push({
                'article_id'   : article.id,
                'article_title': article.title,
                'author_id'    : author.id,
                'author_name'  : author.name,
                'country_code' : author.country_code,
                'country_name' : country
            })
            
        });
    
        return data
    }

    addArticle = function(article_title, author_name){
        var authorID = this.getAuthorIdByName(author_name)
        if(authorID == -1){
            authorID = this.addAuthor(author_name)
        }
        var id = this.getNextArticleID() + 1

        articles.push({ 
            "id"        :id, 
            "author_id" : authorID, 
            "title"     : article_title
        });
        saveJson(articles, _ARTICLES_PATH)
        return id
    }


    updateArticle = function(id, title, author_name){
        var authorID = this.getAuthorIdByName(author_name)
        if(authorID == -1){
            authorID = this.addAuthor(author_name)
        }
        
        for(let i in articles){
            if(articles[i].id == id){
                articles[i].title  = title
                articles[i].author_id = authorID
            }
        }
        saveJson(articles, _ARTICLES_PATH)
        return id
    }


    updateMultipleArticles = function(ids, titles, authors_names){
        console.log(ids, titles, authors_names)
        if(ids.length == titles.length && ids.length == authors_names.length){
            for(let i in ids)
                this.updateArticle(ids[i], titles[i], authors_names[i])
            return true;
        }
        return false;
    }


    removeArticles = function(ids){
        for(let i in ids)
            for(let j in articles)
                if(ids[i] == articles[j].id)
                    articles.splice(j, 1)
        saveJson(articles, _ARTICLES_PATH)
    }


    getNextArticleID = function(){
        let max = 0
        for(let i in articles){
            if(i === 0)
                max = articles[i].id
            else
                max = max < articles[i].id ? articles[i].id : max
        }
        return max
    }


    getAuthorIdByName = function(author_name){
        for(let i in authors){
            if(authors[i].name === author_name)
                return authors[i].id 
        }
        return -1;
    }
    
    addAuthor = function(author_name, country_code = ''){
        var id = this.getNextAuthorID() + 1
        authors.push({
            "id"      : id, 
            "name"    : author_name, 
            "country_code" : country_code.toUpperCase});
        saveJson(authors, _AUTHORS_PATH)
        return id
    }


    updateMultipleAuthors = function(ids, names, country_codes){
        if(ids.length == names.ids && ids.length == country_codes.length){
            for(let i in ids)
                this.updateAuthor(ids[i], names[i], country_codes[i])
            return true;
        }
        return false;
    }


    updateAuthor = function(id, name, country_code = ''){
        var id = -1;
        for(let i in authors){
            if(authors[i].id == id){
                authors[i].name = name;
                authors[i].country_code = country_code;
            }
        }
        saveJson(authors, _AUTHORS_PATH)
        return id;
    }

    
    getNextAuthorID = function(){
        var max = 0
        for(let i in authors){
            if(i === 0)
                max = authors[i].id
            else      
                max = (max < authors[i].id) ? authors[i].id : max
        }
        return max
    }

}