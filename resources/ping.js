const f = document.currentScript.getAttribute('data-fun')
    new Image().src = f + 
        '?u=' + encodeURI(location.href) + 
        '&r=' + encodeURI(document.referrer) + 
        '&d=' + screen.width;