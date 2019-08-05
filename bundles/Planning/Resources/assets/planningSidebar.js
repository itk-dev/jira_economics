import Vue from 'vue';
import axios from 'axios';

(function () {
    new Vue({
        el: '#planningBoardApp',
        data: {
            boards: [],
            filter: ''
        },
        computed: {
            sortedBoards: function () {
                if (this.boards === undefined) {
                    return [];
                }

                let arr = this.boards.filter(function (item) {
                    return item.name.toLowerCase()
                        .indexOf(this.filter) !== -1;
                }.bind(this));

                arr = arr.sort(function (a, b) {
                    return (a.name.toLocaleLowerCase() > b.name.toLocaleLowerCase()) ? 1 : -1;
                });

                return arr;
            }
        },
        created: function () {
            axios.get('/planning/board')
                .then(function (response) {
                    this.boards = response.data.boards;
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                });
        },
        methods: {}
    });
})();