<div class="form-group">
    <label for="sku">SKU</label>
    <input id="sku" type="text" class="form-control" name="SKU" placeholder="Складской номер" value="{{ $product->SKU or '' }}" required>
</div>

<div class="form-group">
    <label for="name">Наименование</label>
    <textarea name="Name" class="form-control" id="name" rows="3" placeholder="Название позиции" required>{{ $product->Name or '' }}</textarea>
</div>

<div class="form-group">
    <label for="price">РРЦ</label>
    <input type="number" id="price" class="form-control" name="Price" placeholder="РРЦ" value="{{ $product->Price or '' }}" required>
</div>

<div class="form-group">
    <label for="link">Ссылка</label>
    <input type="text" id="link" class="form-control" name="Link" placeholder="Ссылка на товар в Hotline" value="{{ $product->Link or '' }}" required>
</div>

<input type="submit" class="btn btn-primary" value="Сохранить">