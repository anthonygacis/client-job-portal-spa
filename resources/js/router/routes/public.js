import guest from "@/js/router/middlewares/guest";


const routes = [];

export default routes.map((route) => {
    const meta = {
        public: true,
        onlyLoggedOut: false,
        exist: true,
        middleware: [
            guest
        ]
    };

    return {...route, meta};
});
