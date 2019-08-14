import Vue from 'vue';
import axios from 'axios';

(function () {
    new Vue({
        el: '#planningBoardApp',
        data: {
            boards: [],
            filter: '',
            baseUrl: '',
            loading: false
        },
        computed: {
            // Sorted by name.
            filteredBoards: function () {
                if (this.boards === undefined) {
                    return [];
                }

                let arr = this.boards.filter(function (item) {
                    return item.name.toLowerCase()
                        .indexOf(this.filter.toLowerCase()) !== -1;
                }.bind(this));

                arr = arr.sort(function (a, b) {
                    return (a.name.toLocaleLowerCase() > b.name.toLocaleLowerCase()) ? 1 : -1;
                });

                return arr;
            }
        },
        created: function () {
            // eslint-disable-next-line no-undef
            this.apiUrl = PLANNING_API_URL;
            // eslint-disable-next-line no-undef
            this.baseUrl = PLANNING_URL;

            this.loading = true;

            axios.get(this.apiUrl + '/board')
                .then(function (response) {
                    this.boards = response.data.boards;
                    this.loading = false;
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                    this.loading = false;
                }.bind(this));
        },
        methods: {}
    });
})();
