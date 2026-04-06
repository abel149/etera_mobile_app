@php
    $globeBrands = $brands ?? \App\Models\Brand::select('name')->pluck('name')->toArray();
@endphp

<section class="world-section">
    <div class="cloud-container">
        <canvas width="500" height="500" id="brandCanvas">
            Your browser does not support canvas.
        </canvas>
        <div id="brandTags">
            <ul>
                @foreach($globeBrands as $brand)
                    <li>
                        <a href="#">
                            <img src="{{ asset("assets/images/brands/" . (is_string($brand) ? $brand : $brand->name) . ".png") }}"
                                 alt="{{ ucfirst(is_string($brand) ? $brand : $brand->name) }}"
                                 width="60"
                                 height="60">
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
