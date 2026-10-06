(function () {
  'use strict';

  function resizeFrame(frame) {
    try {
      var doc = frame.contentDocument;
      if (!doc || !doc.documentElement || !doc.body) {
        return;
      }

      var height = Math.max(
        doc.documentElement.scrollHeight,
        doc.documentElement.offsetHeight,
        doc.body.scrollHeight,
        doc.body.offsetHeight
      );

      if (height > 0) {
        frame.style.height = Math.max(height, 680) + 'px';
      }
    } catch (error) {
      // The route is same-origin; keep the CSS fallback if a browser blocks access.
    }
  }

  function observeFrame(frame) {
    var observer;
    var resize = function () {
      window.requestAnimationFrame(function () {
        resizeFrame(frame);
      });
    };

    var connect = function () {
      resize();

      try {
        if ('ResizeObserver' in window && frame.contentDocument && frame.contentDocument.body) {
          if (observer) {
            observer.disconnect();
          }

          observer = new ResizeObserver(resize);
          observer.observe(frame.contentDocument.body);
          observer.observe(frame.contentDocument.documentElement);
        }
      } catch (error) {
        // The iframe keeps a usable minimum height when observation is unavailable.
      }

      window.setTimeout(resize, 250);
      window.setTimeout(resize, 1000);
    };

    frame.addEventListener('load', connect);

    if (frame.contentDocument && frame.contentDocument.readyState === 'complete') {
      connect();
    }
  }

  function init() {
    document.querySelectorAll('.saber-npc-embed__frame').forEach(observeFrame);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
