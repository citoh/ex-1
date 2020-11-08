/*
The exercise can be found in https://github.com/rubenfil/ex-1.

Submit your changes with a pull request from a different branch.
For the sake of this exercise, data should be retrieved and/or updated 
from the JSON files inside the "data" folder.
Please do the tasks below.

1. Create an endpoint for each of these:
  a. Return a list of authors with their articles and country.
  b. Add a new article with an author that might or might now exist.
      If the author does not exist then it should be created with an empty country.
  c. Update one or multiple articles title and author.
  d. Delete one or multiple articles.

2. Create a middleware for endpoint "a", that will look for pagination parameters 
in the request URL and create an object with it. Endpoint with parameters example:
http://localhost:8080/endpoint_a?per_page=5&page=2&order=name&sort=desc

Then use this object inside endpoint "a" to filter which authors will be returned.
*/


const http = require('http')
const bodyParser = require("body-parser")
const app = require("./functions/app")

const port = 3000
const host = 'localhost'

const allowHostAccessList = ["*"]

const baseMiddlewares = [
  bodyParser.json({
    limit: "20mb"
  })
]

var server = http.createServer( app( allowHostAccessList ) )
server.listen(port, host)
server.on('listening', function() {
    console.log('App listening on http://' + server.address().address + ':' + server.address().port)
});