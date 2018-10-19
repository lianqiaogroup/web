var static = require('node-static');
var fileServer = new static.Server('.', { cache: -1 });
var qs = require('qs');
var mockP = require('./source/vue/mock/products.json');
require('http').createServer(function (request, response) {
    var match;
    switch (request.url) {
        case '/admin/index/style_stat':
            return response.end(JSON.stringify([{ name: "Style 1", view: 10, buy: 7 }, { name: "Style 2", view: 20, buy: 5 }, { name: "Style 3", view:12, buy: 8 }]));
        case '/admin/index/basic_stat':
            return response.end(JSON.stringify([1, 2, 3, 4, 5]));
    }
    if (match = request.url.match(/^\/admin\/products\/list\??(.+)$/)) {
        var args_1 = qs.parse(match[1]);
        var mock_1 = JSON.parse(JSON.stringify(mockP));
        mock_1.page = ~~args_1.page || 1;
        if (args_1.withDel != "true")
            mock_1.list = mock_1.list.filter(function (x) { return x.id % 2 == 0; });
        mock_1.list.forEach(function (x) { x.name = args_1.keyword || "name"; x.id = mock_1.page + "-" + x.id; });
        setTimeout(function () { response.end(JSON.stringify(mock_1)); }, 2000);
        return;
    }
    if (request.url == "/echo" || request.url == "/echo/") {
        request.pipe(response);
    }
    else if (match = request.url.match(/^\/echo\?(.+)$/)) {
        /* handles
               http://127.0.0.1:8080/echo?{%22Content-Type%22:%20%22text/html%22} with post data
           correctly */
        var header = JSON.parse(decodeURIComponent(match[1]));
        Object.keys(header).forEach(function (key) {
            response.setHeader(key, header[key]);
        });
        request.pipe(response);
    }
    else {
        request.addListener('end', function () {
            fileServer.serve(request, response, function (err, result) {
                if (err) {
                    console.error("Error serving " + request.url + " - " + err.message);
                    response.writeHead(err.status, err.headers);
                    response.end();
                }
            });
        }).resume();
    }
}).listen(8080);
