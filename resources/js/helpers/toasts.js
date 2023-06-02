import {useToast} from "vue-toastification";

let myToast = useToast()
function toast(toastType, content) {
    if(toastType == 'success'){
        myToast.success(content)
    }else if(toastType == 'error'){
        myToast.error(content)
    }else if(toastType == 'warning'){
        myToast.warning(content)
    }else if(toastType == 'info'){
        myToast.info(content)
    }
}

export {toast}
