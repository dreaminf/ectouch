import VueRouter from 'vue-router';

let routes = [
    {path: '/', component: require('./pages/home/Page')},
    {path: '/catelog', component: require('./pages/category/Catelog')},
];

const router = new VueRouter({
    mode: 'history',
    routes
});

export default router
