<template>
    <div class="panel-body">

        <button v-if="!scaning" @click="startParser" id="start" class="btn btn-success">Старт <i class="far fa-play-circle"></i></button>
        <p v-else class="parser">
            Идет процес сбора данных...
            <i style="vertical-align: middle" class="fas fa-cog fa-2x"></i>
        </p>
        <hr>

        <table v-if="reportData.length" class="table table-striped">
            <thead>
            <th class="text-left">№</th>
            <th class="text-center">Название</th>
            <th class="text-right">Действие</th>
            </thead>
            <tbody>
            <tr v-for="(report, index) in reportData">
                <td>{{index + 1}}</td>
                <td>{{report.name}}</td>
                <td class="text-right">
                    <div class="btn-group" role="group">
                        <button @click.prevent="showItem(report.id)" class="btn btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button @click.prevent="downloadItem(report.id)" class="btn btn-success">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button @click.prevent="deleteItem(report.id, index)" class="btn btn-danger">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <p v-else class="text-center">Нет созданных отчетов</p>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                'reportData': [],
                'scaning': false
            }
        },
        mounted() {
            this.refresh();
        },
        methods: {
            refresh: function () {
                axios.get('report/get').then((response) => {
                    this.reportData = response.data;
                });
            },
            deleteItem: function (id, index) {
                if (confirm("Вы действительно хотите удалить отчет?")) {
                    axios.delete('/report/delete/' + id)
                        .then(resp => this.reportData.splice(index, 1))
                        .catch((resp) => {
                            console.log(resp);
                            alert("Не удалось удалить отчет");
                        });
                }
            },
            showItem: function (id) {
                location.href = '/report/' + id;
            },
            downloadItem: function (id) {
                location.href = '/report/download/' + id;
            },
            startParser: function () {
                this.scaning = true;
                let data = {
                    message: 'start'
                };
                axios.post('/start', data)
                    .then(resp => this.refresh())
                    .catch((resp) => {
                        console.log(resp);
                        alert("Не удалось обновить данные отчетов.");
                    });
            }
        }
    }
</script>
<style scoped>
    .parser i {
        font-size: 25px;
        animation-name: spin;
        animation-duration: 3000ms;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
    }
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

</style>
