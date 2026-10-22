const http = require('http');
const zlib = require('zlib');

// Node.js service using Brotli compression
const server = http.createServer((req, res) => {
    if (req.url === '/api/data') {
        const data = JSON.stringify({ message: 'Hello from Node!', time: Date.now() });
        res.writeHead(200, {
            'Content-Type': 'application/json',
            'Content-Encoding': 'br'
        });
        const brotli = zlib.createBrotliCompress();
        brotli.pipe(res);
        brotli.write(data);
        brotli.end();
    } else {
        res.writeHead(404);
        res.end('Not found');
    }
});

server.listen(3000, () => {
    console.log('Node service running on port 3000 with Brotli compression');
});
