let f = document.currentScript.getAttribute('data-fun')
if(!f) f = '{self_url}'
    new Image().src = f + 
        '?u=' + encodeURI(location.href) + 
        '&r=' + encodeURI(document.referrer) + 
        '&d=' + screen.width;