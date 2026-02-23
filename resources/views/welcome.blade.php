<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <h1>Products for Tenant: {{ tenant('id') }}</h1>
    <ul>
        @foreach ($products as $product)
        <li>{{ $product->name }} - ${{ $product->price }}</li>
        @endforeach
    </ul>
</body>

</html>