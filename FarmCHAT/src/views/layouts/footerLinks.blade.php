<script src="https://js.pusher.com/7.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@3.0.3/dist/index.min.js"></script>
<script >
    // Gloabl FarmCHAT variables from PHP to JS
    window.FarmCHAT = {
        name: "{{ config('FarmCHAT.name') }}",
        sounds: {!! json_encode(config('FarmCHAT.sounds')) !!},
        allowedImages: {!! json_encode(config('FarmCHAT.attachments.allowed_images')) !!},
        allowedFiles: {!! json_encode(config('FarmCHAT.attachments.allowed_files')) !!},
        maxUploadSize: {{ FarmCHAT::getMaxUploadSize() }},
        pusher: {!! json_encode(config('FarmCHAT.pusher')) !!},
        pusherAuthEndpoint: '{{route("pusher.auth")}}'
    };
    window.FarmCHAT.allAllowedExtensions = FarmCHAT.allowedImages.concat(FarmCHAT.allowedFiles);
</script>
<script src="{{ asset('js/FarmCHAT/utils.js') }}"></script>
<script src="{{ asset('js/FarmCHAT/code.js') }}"></script>
