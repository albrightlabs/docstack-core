/**
 * Dynamic Favicon Generator
 * Generates favicons from emoji or image URLs with optional letter overlay
 */
(function() {
    'use strict';

    window.FaviconGenerator = {
        /**
         * Set favicon from an emoji with optional letter overlay
         */
        fromEmoji: function(emoji, letter, options) {
            options = options || {};
            var size = options.size || 32;
            var letterFont = options.letterFont || 'bold 14px sans-serif';
            var fillStyle = options.fillStyle || 'white';
            var strokeStyle = options.strokeStyle || 'black';
            var padding = options.padding || 2;

            var canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext('2d');

            // Draw emoji as base
            ctx.font = (size - 4) + 'px serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(emoji, size / 2, size / 2 + 2);

            // Draw letter overlay if provided
            if (letter) {
                ctx.font = letterFont;
                ctx.textAlign = 'right';
                ctx.textBaseline = 'bottom';
                ctx.lineWidth = 2;
                var x = size - padding;
                var y = size - padding;
                ctx.strokeStyle = strokeStyle;
                ctx.strokeText(letter, x, y);
                ctx.fillStyle = fillStyle;
                ctx.fillText(letter, x, y);
            }

            this._setFavicon(canvas.toDataURL('image/png'));
        },

        /**
         * Set favicon from an image URL with optional letter overlay
         */
        fromImage: function(imageUrl, letter, options) {
            var self = this;
            options = options || {};
            var size = options.size || 32;
            var font = options.letterFont || 'bold 14px sans-serif';
            var fillStyle = options.fillStyle || 'white';
            var strokeStyle = options.strokeStyle || 'black';
            var padding = options.padding || 2;

            var img = new Image();
            img.crossOrigin = 'anonymous';

            img.onload = function() {
                var canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;
                var ctx = canvas.getContext('2d');

                // Draw the base favicon
                ctx.drawImage(img, 0, 0, size, size);

                // Draw letter overlay if provided
                if (letter) {
                    ctx.font = font;
                    ctx.textAlign = 'right';
                    ctx.textBaseline = 'bottom';
                    ctx.lineWidth = 2;
                    var x = size - padding;
                    var y = size - padding;
                    ctx.strokeStyle = strokeStyle;
                    ctx.strokeText(letter, x, y);
                    ctx.fillStyle = fillStyle;
                    ctx.fillText(letter, x, y);
                }

                self._setFavicon(canvas.toDataURL('image/png'));
            };

            img.onerror = function() {
                // Fallback to emoji if image fails
                self.fromEmoji('📚', letter, options);
            };

            img.src = imageUrl;
        },

        /**
         * Initialize favicon from branding configuration
         */
        init: function(config) {
            config = config || {};
            var faviconUrl = config.faviconUrl || null;
            var faviconEmoji = config.faviconEmoji || null;
            var siteEmoji = config.siteEmoji || '📚';
            var siteName = config.siteName || '';
            var customLetter = config.faviconLetter || null;
            var showLetter = config.faviconShowLetter || false;

            // Determine the letter to show (if any)
            var letter = null;
            if (showLetter) {
                letter = customLetter || (siteName ? siteName.charAt(0).toUpperCase() : null);
            }

            var options = {
                size: 32,
                letterFont: 'bold 16px sans-serif',
                fillStyle: 'white',
                strokeStyle: 'black',
                padding: 1
            };

            // Determine favicon source: custom URL > custom emoji > site emoji
            if (faviconUrl) {
                this.fromImage(faviconUrl, letter, options);
            } else {
                var emoji = faviconEmoji || siteEmoji || '📚';
                this.fromEmoji(emoji, letter, options);
            }
        },

        /**
         * Set the favicon link element
         */
        _setFavicon: function(dataUrl) {
            var link = document.querySelector('link[rel="icon"]');
            if (!link) {
                link = document.createElement('link');
                link.rel = 'icon';
                document.head.appendChild(link);
            }
            link.type = 'image/png';
            link.href = dataUrl;
        }
    };
})();
