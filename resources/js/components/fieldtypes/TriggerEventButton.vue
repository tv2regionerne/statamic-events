<template>
    <div>
        <button
            type="button"
            class="btn"
            @click="triggerEvent"
            v-text="__('Trigger Event')"
        >
        </button>
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        computed: {

        },

        methods: {
            triggerEvent() {
                this.$axios.post(this.meta.post_url, this.meta)
                    .then(() => {
                        this.$toast.success(__("Event triggered"));
                    });
            },

        },
    };
</script>
