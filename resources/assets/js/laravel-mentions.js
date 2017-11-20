'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Mentions = function () {
    function Mentions(options) {
        _classCallCheck(this, Mentions);

        this.options = options;
        this.collections = [];

        this.input = this.findNode(this.options.input, '.has-mentions');
        this.output = this.findNode(this.options.output, '#mentions');

        this.collect().attach().listen();
    }

    _createClass(Mentions, [{
        key: 'findNode',
        value: function findNode(selector, defaultSelector) {
            return document.querySelector(selector || defaultSelector);
        }
    }, {
        key: 'template',
        value: function template(pool) {
            return function (item) {
                return '<a href="'+item.original[pool.profile_url]+'"><span class="mention-node" contenteditable="false" data-object="' + pool.pool + ':' + item.original[pool.reference] + '">' + (pool.trigger || '@') + item.original[pool.display] + '</span></a>';
            };
        }
    }, {
        key: 'values',
        value: function values(pool) {
            return function (text, callback) {
                if (text.length <= 1) return;

                var xhttp = new XMLHttpRequest();

                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        callback(JSON.parse(this.responseText));
                    }
                };

                xhttp.open('get', '/api/mentions?p=' + pool.pool + '&q=' + text, true);
                xhttp.send();
            };
        }
    }, {
        key: 'collect',
        value: function collect() {
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = this.options.pools[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var pool = _step.value;

                    this.collections.push({
                        trigger: pool.trigger || '@',
                        lookup: pool.display,
                        allowSpaces: pool.allowSpaces || true,
                        selectTemplate: this.template(pool),
                        values: this.values(pool)
                    });
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            return this;
        }
    }, {
        key: 'attach',
        value: function attach() {
            

            this.tribute = new Tribute({
                collection: this.collections
            });

            this.tribute.attach(this.input);
          
            return this;
        }
    }, {
        key: 'listen',
        value: function listen() {
            var instance = this;

            this.input.addEventListener('keyup', function (event) {
                var input = event.target;
                var mentions = instance.output;
                var objects = [];

                var nodes = input.getElementsByClassName('mention-node');

                var _iteratorNormalCompletion2 = true;
                var _didIteratorError2 = false;
                var _iteratorError2 = undefined;

                try {
                    for (var _iterator2 = nodes[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                        var node = _step2.value;

                        objects.push(node.getAttribute('data-object'));
                    }
                } catch (err) {
                    _didIteratorError2 = true;
                    _iteratorError2 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion2 && _iterator2.return) {
                            _iterator2.return();
                        }
                    } finally {
                        if (_didIteratorError2) {
                            throw _iteratorError2;
                        }
                    }
                }

                mentions.value = objects.join();

                if (input.hasAttribute('for') && !(instance.options.ignoreFor || false)) {
                    document.querySelector(input.getAttribute('for')).value = input.innerHTML;
                }
            });
        }
    }]);

    return Mentions;
}();    

module.exports = Mentions;