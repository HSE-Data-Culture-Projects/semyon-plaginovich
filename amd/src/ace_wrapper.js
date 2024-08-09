define([], function() {
    return new Promise(function(resolve, reject) {
        if (window.ace) {
            resolve(window.ace);
        } else {
            var script = document.createElement('script');
            script.src = M.cfg.wwwroot + '/question/type/yconrunner/amd/build/ace.js';
            script.onload = function() {
                if (window.ace) {
                    resolve(window.ace);
                } else {
                    reject(new Error('ACE not loaded'));
                }
            };
            script.onerror = function() {
                reject(new Error('Failed to load ACE'));
            };
            document.head.appendChild(script);
        }
    });
});
