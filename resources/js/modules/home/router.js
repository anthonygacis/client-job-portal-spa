const Home = () => import('@/js/modules/home/AppHome.vue')

export default {
    path: "home",
    name: "home",
    alias: ["/app"],
    component: Home,
    meta: {
        title: "Home"
    }
}
