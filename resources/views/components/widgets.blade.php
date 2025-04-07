<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
    @foreach($resource->defineWidgets() as $widgetClass)
        @php
            $widget = new $widgetClass();
            // Controleer of de widget zichtbaar moet zijn op basis van de opgegeven visibility
            if (in_array($visibility, $widget->pageVisibility())) {
                echo $widget->render();
            }
        @endphp
    @endforeach
</div>
