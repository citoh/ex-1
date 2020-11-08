const express    = require('express');
const corsConfig = require('./cors-config')
const JsonQuery = require('./json-query')

var page     = -1 //show all
var perPage = -1 //show all
var order    = "author_name"
var sort     = "asc"

function setPagination(reqQuery){
    page    = typeof reqQuery.page     === 'undefined' ? page    : reqQuery.page
    perPage = typeof reqQuery.per_page === 'undefined' ? perPage : reqQuery.per_page
    order   = typeof reqQuery.order    === 'undefined' ? order   : reqQuery.order
    sort    = typeof reqQuery.sort     === 'undefined' ? sort    : reqQuery.sort
}

module.exports = function( allowHostAccessList ){
    const app = express()
    const jsonQuery = new JsonQuery()

    app.get(['/', '/endpoint_a'],  corsConfig( allowHostAccessList ), function (req, res, next) {
        setPagination(req.query)
        const data = jsonQuery.query(page, perPage, order, sort)
        data.push({message: "OK", status: 200})
        res.send( data )
    });


    app.get('/endpoint_b',  corsConfig( allowHostAccessList ), function (req, res, next) {
        setPagination(req.query)
        if(req.query.title !== 'undefined' && req.query.author !== 'undefined'){
            let title  = req.query.title
            let author = req.query.author
            jsonQuery.addArticle(title, author)
        }
        const data = jsonQuery.query(page, perPage, order, sort)
        data.push({message: "OK", status: 200})
        res.send( data )
    });


    app.get('/endpoint_c',  corsConfig( allowHostAccessList ), function (req, res, next) {
        setPagination(req.query)
        if( typeof req.query.articles_ids     !== 'undefined' && 
            typeof req.query.articles_titles  !== 'undefined' && 
            typeof req.query.articles_authors !== 'undefined' ){
            
            var articles_ids           = req.query.articles_ids
            var titles        = req.query.articles_titles
            var authors_names = req.query.articles_authors
            jsonQuery.updateMultipleArticles(articles_ids, titles, authors_names)

        }
        if( typeof req.query.authors_ids       !== 'undefined' && 
            typeof req.query.authors_names     !== 'undefined' && 
            typeof req.query.authors_countries !== 'undefined' ){
            
            var authors_ids     = req.query.authors_ids
            var names           = req.query.authors_names
            var countries_codes = req.query.authors_countries
            jsonQuery.updateMultipleArticles(authors_ids, names, countries_codes)

        }
        const data = jsonQuery.query(page, perPage, order, sort)
        data.push({message: "OK", status: 200})
        res.send( data )
    });


    app.get('/endpoint_d',  corsConfig( allowHostAccessList ), function (req, res, next) {
        setPagination(req.query)
        if(req.query.ids !== 'undefined'){
            var ids  = req.query.ids
            jsonQuery.removeArticles(ids)
        }
        const data = jsonQuery.query(page, perPage, order, sort)
        data.push({message: "OK", status: 200})
        res.send( data )
    });


    app.use(function (req, res, next) {  
        var data404 = [{ 
            'error'  : '404 - Not Found',
            'status' : '404'
        }]
        res.status(404).send(data404)

        return next()
    });


    app.use(function customErrorHandler(err, req, res, next) {
        var data = [{ 
            'error'  : '401 - Unauthorized access',
            'status' : '401'
        }]
        console.error(err)
        res.status(401).send(JSON.stringify(data))
        return next()
    });


    return app;
}