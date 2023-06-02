import axios from "axios";

axios.defaults.withCredentials = true;

axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if (error.response.status === 401 || error.response.status === 419) {
        localStorage.removeItem('auth')
    }
    return Promise.reject(error);
});

async function requestPost(url, data) {
    return await axios.post(attachApiUrl(url), data)
}

async function requestPut(url, data) {
    return await axios.put(attachApiUrl(url), data)
}

async function requestGet(url) {
    return await axios.get(attachApiUrl(url))
}

async function requestDelete(url, config = null) {
    return await axios.delete(attachApiUrl(url), config)
}

function attachApiUrl(path) {
    let api_path = document.querySelector('meta[name="api-url"]').getAttribute('content')
    if (path) {
        return api_path + path;
    }
    return api_path
}

export {attachApiUrl, requestPost, requestPut, requestGet, requestDelete}
