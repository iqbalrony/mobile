/*!
 * bootstrap-progressbar v0.6.0 by @minddust
 * Copyright (c) 2012-2013 Stephan Gross
 *
 * https://www.minddust.com/bootstrap-progressbar
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
!function(t){"use strict";var e=function(n,a){this.$element=t(n),this.options=t.extend({},e.defaults,a)};e.defaults={transition_delay:300,refresh_speed:50,display_text:"none",use_percentage:!0,percent_format:function(t){return t+"%"},amount_format:function(t,e){return t+" / "+e},update:t.noop,done:t.noop,fail:t.noop},e.prototype.transition=function(){var n=this.$element,a=n.parent(),s=this.$back_text,i=this.$front_text,r=this.options,o=n.attr("aria-valuetransitiongoal"),h=n.attr("aria-valuemin")||0,f=n.attr("aria-valuemax")||100,u=a.hasClass("vertical"),c=r.update&&"function"==typeof r.update?r.update:e.defaults.update,d=r.done&&"function"==typeof r.done?r.done:e.defaults.done,p=r.fail&&"function"==typeof r.fail?r.fail:e.defaults.fail;if(!o)return void p("aria-valuetransitiongoal not set");var l=Math.round(100*(o-h)/(f-h));if("center"===r.display_text&&!s&&!i){this.$back_text=s=t("<span>",{"class":"progressbar-back-text"}).prependTo(a),this.$front_text=i=t("<span>",{"class":"progressbar-front-text"}).prependTo(n);var g;u?(g=a.css("height"),s.css({height:g,"line-height":g}),i.css({height:g,"line-height":g}),t(window).resize(function(){g=a.css("height"),s.css({height:g,"line-height":g}),i.css({height:g,"line-height":g})})):(g=a.css("width"),i.css({width:g}),t(window).resize(function(){g=a.css("width"),i.css({width:g})}))}setTimeout(function(){var t,e,p,g,_;u?n.css("height",l+"%"):n.css("width",l+"%");var v=setInterval(function(){u?(p=n.height(),g=a.height()):(p=n.width(),g=a.width()),t=Math.round(100*p/g),e=Math.round(p/g*(f-h)),t>=l&&(t=l,e=o,d(),clearInterval(v)),"none"!==r.display_text&&(_=r.use_percentage?r.percent_format(t):r.amount_format(e,f),"fill"===r.display_text?n.text(_):"center"===r.display_text&&(s.text(_),i.text(_))),n.attr("aria-valuenow",e),c(t)},r.refresh_speed)},r.transition_delay)};var n=t.fn.progressbar;t.fn.progressbar=function(n){return this.each(function(){var a=t(this),s=a.data("bs.progressbar"),i="object"==typeof n&&n;s||a.data("bs.progressbar",s=new e(this,i)),s.transition()})},t.fn.progressbar.Constructor=e,t.fn.progressbar.noConflict=function(){return t.fn.progressbar=n,this}}(window.jQuery);