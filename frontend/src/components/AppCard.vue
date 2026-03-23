<template>
    <div class="card">
        <slot />
    </div>
</template>


<style scoped>
.card {
    background: white;
    border-radius: var(--radius-card);
    padding: 2.5rem;
    box-shadow: var(--shadow-card);
}
</style>