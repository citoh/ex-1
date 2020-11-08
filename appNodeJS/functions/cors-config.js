const cors = require("cors")
const { response } = require("express")

module.exports = function( allowHostAccessList ){

    var corsOptions = {
      origin: function (origin, callback) {
        if (allowHostAccessList.indexOf(origin) !== -1 || allowHostAccessList.indexOf("*") !== -1) {
          callback(null, true)
        } else {
          callback( 401, true )
        }
      }
    }

    return cors(corsOptions)
  
}