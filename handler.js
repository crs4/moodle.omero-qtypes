
self.addEventListener('message', function(e) {
    console.log("Handler is sending: " + e.data);
    self.postMessage(e.data);
}, false);