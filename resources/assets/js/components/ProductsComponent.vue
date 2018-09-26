<template>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <!-- Button trigger modal -->
                    <button type="button" @click.prevent="add" class="btn btn-primary pull-right" data-toggle="modal" data-target="#myModal">
                        <i class="fas fa-plus"></i> Добавить позицию
                    </button>
                    <button type="button" @click.prevent="add" class="btn btn-success" data-toggle="modal" data-target="#myModal-import">
                        <i class="fas fa-file-excel"></i> Импорт
                    </button>
                </div>

                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <th class="text-left">SKU</th>
                        <th class="text-center">Наименование</th>
                        <th class="text-center">РРЦ</th>
                        <th class="text-center">Норма</th>
                        <th class="text-right">Действие</th>
                        </thead>
                        <tbody>
                        <tr v-for="(product, index) in productData">
                            <td>{{product.SKU}}</td>
                            <td>{{product.Name}}</td>
                            <td class="text-center">{{product.Price}}</td>
                            <td class="text-center">{{product.Price - product.Price * 0.05}}</td>
                            <td class="text-right">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" @click.prevent="edit(index)" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <button type="button" @click.prevent="deleteItem(product.id, index)" class="btn btn-danger">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 v-if="editor" class="modal-title" >Редактировать позицию</h4>
                        <h4 v-else class="modal-title">Добавить позицию</h4>
                    </div>
                    <form action="" method="post">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input id="sku" type="text" v-model="SKU"  class="form-control" name="SKU" placeholder="Складской номер" value="" required>
                                <ul class="error-wrapper" v-if="errors.SKU">
                                    <li class="error" v-for="error in errors.SKU">{{error}}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label for="name">Наименование</label>
                                <textarea name="Name" v-model="Name" class="form-control" id="name" rows="3" placeholder="Название позиции" required></textarea>
                                <ul class="error-wrapper" v-if="errors.Name">
                                    <li class="error" v-for="error in errors.Name">{{error}}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label for="price">РРЦ</label>
                                <input type="number" v-model="Price" id="price" class="form-control" name="Price" placeholder="РРЦ" value="" required>
                                <ul class="error-wrapper" v-if="errors.Price">
                                    <li class="error" v-for="error in errors.Price">{{error}}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label for="link">Ссылка</label>
                                <input type="text" v-model="Link" id="link" class="form-control" name="Link" placeholder="Ссылка на товар в Hotline" value="" required>
                                <ul class="error-wrapper" v-if="errors.Link">
                                    <li class="error" v-for="error in errors.Link">{{error}}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                            <button type="submit" v-if="!editor" @click.prevent="store" class="btn btn-primary">Сохранить</button>
                            <button type="submit" v-else @click.prevent="update" class="btn btn-primary">Обновить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="myModal-import" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Импорт позиций</h4>
                    </div>
                    <form action="" method="post">
                        <div class="modal-body">
                            <label for="file">Выберите файл Excel:</label>
                            <input @change="fileChange" type="file" ref="fileupload" id="file" name="file" required>
                            <ul class="error-wrapper" v-if="errors.file">
                                <li class="error" v-for="error in errors.file">{{error}}</li>
                            </ul>
                            <br>
                            <input v-model="importData.reset" type="checkbox" name="reset_all" value="true" id="reset">
                            <label for="reset">Удалить все позиции перед импортом</label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                            <button type="submit" @click.prevent="loadFile" class="btn btn-primary">Импорт</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                'productData': [],
                'importData': {
                    name: null,
                    file: null,
                    reset: false
                },
                'errors': [],
                'editor': false,
                'id': null,
                'SKU': null,
                'Name': null,
                'Price': null,
                'Link': null,
                'index': null
            }
        },
        mounted() {
            this.refresh();
        },
        methods: {
            refresh: function () {
                axios.get('product/get').then((response) => {
                    this.productData = response.data
                });
            },
            fileChange: function () {
                this.importData.file = event.target.files[0];
                this.importData.name = this.importData.file.name;
            },
            loadFile: function () {
                const formData = new FormData();
                formData.append('name', this.importData.name);
                formData.append('file', this.importData.file);
                formData.append('reset', this.importData.reset);
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                };
                axios.post('/product/import', formData, config)
                    .then((response) => {
                        $('#myModal-import').modal('hide');
                        this.clear();
                        this.refresh();
                        //alert('Все позиции успешно добавлены.')
                        //console.log(response);
                    })
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error);
                            alert('Не удалось добавить новую позицию')
                        }
                    });
            },
            add: function () {
                this.editor = false;
                this.clear();
            },
            edit: function (index) {
                this.editor = true;
                this.id = this.productData[index].id;
                this.SKU = this.productData[index].SKU;
                this.Name = this.productData[index].Name;
                this.Price = this.productData[index].Price;
                this.Link = this.productData[index].Link;
                this.index = index;
            },
            store: function () {
                let data = {
                    SKU: this.SKU,
                    Name: this.Name,
                    Price: this.Price,
                    Link: this.Link
                };
                axios.post('/product/store', data)
                    .then((response) => {
                        this.productData.push(data);
                        $('#myModal').modal('hide');
                        this.clear();
                    })
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error);
                            alert('Не удалось добавить новую позицию')
                        }
                    });
            },
            update: function () {
                let data = {
                    SKU: this.SKU,
                    Name: this.Name,
                    Price: this.Price,
                    Link: this.Link
                };
                if (!this.id) {
                    alert('Нет id в буфере!!!')
                }
                axios.patch('/product/edit/' + this.id, data)
                    .then((response) => this.productData.splice(this.index, 1, data))
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error);
                            alert('Не удалось добавить обновить позицию');
                        }
                    });

                $('#myModal').modal('hide');
                this.clear();
            },
            deleteItem: function (id, index) {
                if (confirm("Вы действительно хотите удалить позицию?")) {
                    axios.delete('/product/delete/' + id)
                        .then(resp => this.productData.splice(index, 1))
                        .catch((resp) => {
                            console.log(resp);
                            alert("Не удалось удалить позицию");
                        });
                }
            },
            clear: function () {
                this.SKU = null;
                this.Name = null;
                this.Price = null;
                this.Link = null;

                const input = this.$refs.fileupload;
                input.type = 'text';
                input.type = 'file';

                this.importData.file = null;
                this.importData.name = null;
                this.importData.reset = false;
            }
        }
    }
</script>

<style scoped>
.error-wrapper {
    padding-left: 15px;
    margin-bottom: 0;
}
.error {
    color: #a94442;
    font-size: 12px;
}
</style>