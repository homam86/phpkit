
(function ( $ ) {
    
    $.widget( "insider.overlay", {
        
        $holder : null,
        $overlay : null,
        $dialog : null,
        
        options : {
            fixed: false,
            width : '600px',
            height : '500px',
            borderRadius : '15px'
        },
        
        _create: function() {
            this.$overlay = $(Insider.html({
                tag     : 'div',
                style   : this.overlayStyle(),
                content : ''
            }));
            
            this.$holder = $(Insider.html({
                tag     : 'div',
                style   : this.holderStyle(),
                content : ''
            }));
            
            this.$dialog = $(Insider.html({
                tag     : 'div',
                style   : this.dialogStyle(),
                content : ''
            }));
            
            var $close = $(Insider.html([
                {
                    tag : 'style',
                    content : [
                        '#insider_overlay_close {',
                            'color: #B4B4B9;',
                            'font-size: 25px;',
                            'font-weight: bold;',
                            'display: block;',
                            'position: absolute;',
                            'top: 5px;',
                            'right: 5px;',
                        '}\n',
                        '#insider_overlay_close:hover {',
                            'color: #a00;',
                            'text-decoration: none;',
                        '}'
                    ]
                }, {
                    tag     : 'a',
                    href    : 'javascript:void(0)',
                    id      :'insider_overlay_close',
                    content : '&#x26CC',
                    //onclick : "$('"+this.element.selector+"').overlay('hide')"
                }
            ]));
            
            $close.appendTo(this.$dialog);
            this.element.appendTo(this.$dialog);
            
            this.$overlay.appendTo('body');
            this.$dialog.appendTo(this.$holder);
            this.$holder.appendTo('body');
            
            
            var plugin = this;
            $('#insider_overlay_close').click(function(){
                plugin.hide();
            });
            //this.show();
        },
        
        show:function() {
            this.center(this.$dialog);
            this.$overlay.show();
            this.$holder.fadeIn(700);
        },
        
        hide:function() {
            this.$overlay.hide();
            this.$holder.fadeOut();
        },
        
        overlayStyle: function() {
            return {
                display: 'none',
                position: 'fixed',
                left: '0px',
                top: '0px',
                width:'100%',
                height: this.clientHeight()+'px',
                'text-align':'center', 'z-index': 1000,
                'background-color' : '#777', opacity: 0.3
            }
        },
        
        holderStyle : function() {
            return {
                display: 'none',
                left: '0px', top: '0px', width:'100%',
                'z-index': 1111,
                height: this.clientHeight()+'px',
                'position' : this.options.fixed ? 'fixed' : 'absolute'
            }
        },
        
        dialogStyle : function() {
            return {
                //top: this.topOffset(),
                width:this.options.width,
                'min-height':this.options.height,
                padding:'15px',
                margin: '100px auto',
                background: '#fff',
                 'position' : 'relative',
                'border-radius': this.options.borderRadius,
                'box-shadow' : '#113 0 0 20px',
            }
        },
    
        clientHeight: function() {
            return $(document).height();  
        },
        
        topOffset: function() {
            return $(window).scrollTop()
        },
        
        center: function(obj) {
            var top = $(window).scrollTop();
            obj.css({
                position:'absolute',
                left: ($(window).width() - obj.outerWidth())/2,
                top: top//+($(window).height() - obj.outerHeight())/2
            });
        }
    });
 
}( jQuery ));


jQuery(document).ready(function(){
    
});