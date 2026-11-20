<div
    x-data="{
        listingType: @js(old('listing_type', $listing?->listing_type ?? 'product')),
        categoryId: @js((string) old('category_id', $listing?->category_id)),
        subcategoryId: @js((string) old('subcategory_id', $listing?->subcategory_id)),
        categories: @js($categories->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'type' => in_array($category->parent?->slug, ['products', 'services'], true)
                ? ($category->parent->slug === 'products' ? 'product' : 'service')
                : null,
        ])->values()),
        subcategories: @js($subcategories),
        previews: [],
    }"
    class="space-y-5"
>
    <div>
        <label for="listing_type" class="block text-sm font-medium text-slate-700">Product or service</label>
        <select id="listing_type" name="listing_type" x-model="listingType" @change="categoryId = ''; subcategoryId = ''" required class="mt-1 block w-full rounded-lg border-sand">
            @foreach (['product' => 'Product', 'service' => 'Service'] as $value => $label)
                <option value="{{ $value }}" @selected(old('listing_type', $listing?->listing_type ?? 'product') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('listing_type') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Name / title</label>
        <input id="title" name="title" value="{{ old('title', $listing?->title) }}" required maxlength="255" class="mt-1 block w-full rounded-lg border-sand">
        @error('title') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="5" required maxlength="10000" class="mt-1 block w-full rounded-lg border-sand">{{ old('description', $listing?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="category_id" class="block text-sm font-medium text-slate-700">Category</label>
            <select id="category_id" name="category_id" x-model="categoryId" @change="subcategoryId = ''" required class="mt-1 block w-full rounded-lg border-sand">
                <option value="">Choose a category</option>
                <template x-for="category in categories.filter(item => !item.type || item.type === listingType)" :key="category.id">
                    <option :value="category.id" x-text="category.name"></option>
                </template>
            </select>
            @error('category_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="subcategory_id" class="block text-sm font-medium text-slate-700">Subcategory (optional)</label>
            <select id="subcategory_id" name="subcategory_id" x-model="subcategoryId" :disabled="!categoryId" class="mt-1 block w-full rounded-lg border-sand disabled:bg-paper">
                <option value="">No subcategory</option>
                <template x-for="subcategory in subcategories.filter(item => String(item.parent_id) === String(categoryId))" :key="subcategory.id">
                    <option :value="subcategory.id" x-text="subcategory.name"></option>
                </template>
            </select>
            @error('subcategory_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ priceType: @js(old('price_type', $listing?->price_type ?? 'fixed')) }">
            <label for="price_type" class="block text-sm font-medium text-slate-700">Price type</label>
            <select id="price_type" name="price_type" x-model="priceType" required class="mt-1 block w-full rounded-lg border-sand">
                @foreach (['fixed' => 'Fixed', 'negotiable' => 'Negotiable', 'contact' => 'Contact for price'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('price_type') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror

            <label for="price" class="mt-4 block text-sm font-medium text-slate-700">Price (INR)</label>
            <input id="price" name="price" type="number" step="0.01" min="0" max="9999999999.99" value="{{ old('price', $listing?->price) }}" :required="priceType !== 'contact'" :disabled="priceType === 'contact'" class="mt-1 block w-full rounded-lg border-sand disabled:bg-paper">
            @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        @foreach (['country' => 'Country', 'state' => 'State', 'city' => 'City', 'area' => 'Area (optional)'] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $listing?->$field) }}" @required($field !== 'area') class="mt-1 block w-full rounded-lg border-sand">
                @error($field) <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
        @endforeach
    </div>

    <div>
        <label for="images" class="block text-sm font-medium text-slate-700">Photos (up to 10 images, 5 MB each)</label>
        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple @change="previews = Array.from($event.target.files).map(file => URL.createObjectURL(file))" class="mt-2 block w-full text-sm text-slate-700">
        @error('images') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        @foreach ($errors->getMessages() as $field => $messages)
            @if (str_starts_with($field, 'images.'))
                @foreach ($messages as $message)
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @endforeach
            @endif
        @endforeach
        <div x-show="previews.length" x-cloak class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <template x-for="(preview, index) in previews" :key="preview">
                <img :src="preview" :alt="`Selected image ${index + 1}`" class="h-28 w-full rounded-lg object-cover">
            </template>
        </div>
    </div>
</div>
