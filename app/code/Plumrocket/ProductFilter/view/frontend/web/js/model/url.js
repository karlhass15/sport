/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Product Filter v3.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    "jquery"
], function($){
    "use strict";

    return {

        separator: '=',
        isSeoFriendly: true,
        categoryUrlSufix: '',
        currentUrl: '',

        getManualUrl: function(options, url)
        {
            var self = this;

            Object.keys(options)
                .sort()
                .forEach(function(request, i) {
                    var params = options[request];
                    params.sort();
                    if (self.isSeoFriendly) {
                        $.each(params, function(key, value) {
                            url = self.getUrl(request, value, null, url);
                        });
                    } else {
                        var value = params.join(',');
                        url = self.getUrl(request, value, null, url);
                    }
                });

            return url;
        },

        beforeProcess: function(url) {
            if (this.isSeoFriendly) {
                url = url.replace(this.categoryUrlSufix,'');
                var p = url.indexOf('?');
                if (p > 0) {
                    url = this.sortParams(url.substr(0, p)) + this.categoryUrlSufix + url.substr(p);
                } else {
                    url = this.sortParams(url) + this.categoryUrlSufix;
                }
            } else {
                var p = url.indexOf('?');
                if (p > 0) {
                    url = url.substr(0, p) + this.sortGetParams(url.substr(p));
                }
            }

            return url;
        },

        sortParams: function(url) {
            var attrSeparator = '/';
            var currentUrl = this.currentUrl.replace(this.categoryUrlSufix,'');
            if (currentUrl === url) {
                return url;
            }
            var p1 = currentUrl.indexOf('?');
            if (p1 > 0) {
                currentUrl = url.substr(0, p1);
            }

            if (url.indexOf(currentUrl) === 0) {
                var paramString = url.replace(currentUrl, '');
                var startSymbol = '';
                if (paramString.charAt(0) === attrSeparator) {
                    paramString = paramString.slice(1);
                }
                var params = paramString.split(attrSeparator);
                if (params.length > 1) {
                    params.sort(function(a, b){
                        var aPos = a.indexOf(this.separator);
                        var bPos = b.indexOf(this.separator);

                        if (aPos === -1 || bPos === -1) {
                            return 0;
                        }
                        return (a.substr(0, aPos) > b.substr(0, bPos)) ? 1 : -1;
                    }.bind(this));
                    var newParamString = params.join(attrSeparator);
                    url = url.replace(paramString, newParamString);
                }
            }
            return url;
        },

        sortGetParams: function (urlPart) {
            if (urlPart.indexOf('&') === -1) {
                return urlPart;
            }

            var paramString = urlPart;
            if (paramString.charAt(0) === '?') {
                paramString = paramString.slice(1);
            }

            var endPos = paramString.indexOf('#');
            if (endPos > 0) {
                paramString = paramString.substr(0, endPos)
            }

            var params = paramString.split('&');
            params.sort(function (a, b) {
                var aSrt = a;
                var bSrt = b;
                if (a.indexOf('=') > 0) {
                    aSrt = a.split('=')[0];
                }
                if (b.indexOf('=') > 0) {
                    bSrt = b.split('=')[0];
                }
                if (aSrt === bSrt) {
                  return 0;
                }
                return (aSrt > bSrt) ? 1 : -1;
            });

            var newParamString = params.join('&');

            return urlPart.replace(paramString, newParamString);
        },

        removePriceFromUrl: function(url) {
            var priceFilters = url.match(/(\/price-[A-Za-z0-9_.,%]+)/g);
            if (priceFilters) {
                priceFilters = jQuery.unique(priceFilters);
                if (priceFilters.length > 1) {
                    for (var i = 0; i < priceFilters.length - 1; i++ ) {
                        url  = url.replace(priceFilters[i], '');
                    }
                }
            }
            return url;
        },

        getCurrentUrl: function() {
            return window.location.href.replace(window.location.search, '');
        },

        isParams: function() {
            return window.location.search.length;
        },

        getParamsFromUrl: function() {
            var query = location.search.substr(1);
            var result = {};

            if (query) {
                query.split("&").forEach(function(part) {
                    var item = part.split("=");
                    result[item[0]] = [];
                    var params = decodeURIComponent(item[1]).split(',');
                    params.forEach(function(param) {
                        result[item[0]].push(param);
                    });
                });
            }
            return result;
        },

        convertValue: function(val) {
            val = val.toLowerCase();
            val = val.replace('-', '_');
            val = val.replace(' ', '_');
            return val;
        },

        //Method copied from jquery.param function
        param: function( a ) {

            var self = this;

            var prefix,
                s = [],
                add = function( key, value ) {
                    //Add parameters to url
                    value = $.isFunction( value ) ? value() : ( value == null ? "" : value );
                    s[ s.length ] = encodeURIComponent( key ) + self.separator + encodeURIComponent( value );
                };
            //For each parameters
            //Parameter key is name of var
            $.each( a, function(name, value) {
                add( name, value );
            });
            //Check is seo friendly url enabled
            var joinParam = self.isSeoFriendly ? '/' : '&';

            return s.join( joinParam ).replace( "/%20/g", "+" );
        },

        getUrl: function (pName, pValue, defValue, url, remove) {

            if (typeof pName == 'undefined') {
                return url;
            }

            if (typeof url == 'undefined') {
                var urlPaths = document.location.href.split('?');
            } else {
                var urlPaths = url.split('?');
            }
            var pathname = urlPaths[0];

            var decode = window.decodeURIComponent;
                // pathname = window.location.pathname,
                /*urlPaths = document.location.href.split('?');*/

            if (!this.isSeoFriendly) {
                //If seo friendly url not used, then split by ampersant
                var urlParams = urlPaths[1] ? urlPaths[1].split('&') : [];
            } else {
                //If using seo friendly url, then detect parameters
                //And split by slash
                var _urlParams = pathname.split('/')
                    urlParams = [];

                for (var i = _urlParams.length - 1; i > 0 ; i--) {
                    //If param look like our parameters (i mean parameter has our separator)
                    //In this case we consider, that it is parameter
                    if (_urlParams[i].search(this.separator) > 0) {
                        //If paremeter not aplied currently
                        if (!pathname.search(_urlParams[i]))
                            urlParams.push(_urlParams[i]);
                    } else {
                        //Search from the end of url path and if there no sesaparator break loop
                        //It means that next parameters not consided to filter options
                        break;
                    }
                }
            }

            var pData = {},
                baseUrl = urlPaths[0],
                parameters;

            var _separator = this.isSeoFriendly ? this.separator : '=';
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split(_separator);
                //Creating array with parameters
                //Key is parameter name, value is value =)
                pData[decode(parameters[0])] = parameters[1] !== undefined ? decode(parameters[1].replace(/\+/g, '%20')) : '';
            }
            pData[pName] = pValue;
       /*     if (pValue == defValue) {
                delete pData[pName];
            }*/

            //Split all parameters to sting
            //this.param based on jQuery.param
            pData = this.param(pData);

            //Build final url
            if (this.isSeoFriendly) {

                if (remove) {
                    var regex = new RegExp("(\/" + pName + "-[a-z0-9]+)");
                    baseUrl = baseUrl.replace(regex, "");
                }

                baseUrl = baseUrl.replace("/" + pName + '-' + pValue, "");

                //If last symbol of url not slash
                var slash = pathname[pathname.length-1] != '/' ? '/' :'';
                //Add parameters to base url
                var actionUrl = baseUrl + (pData.length ? slash + pData : '');
                //Add get parameters if it exists
                //
                actionUrl += urlPaths[1] ? '?' + urlPaths[1] : '';
            } else {
                var actionUrl = baseUrl + (pData.length ? '?' + pData : '');
            }

            //Send ajax request
            return actionUrl;
        }
    }
});
