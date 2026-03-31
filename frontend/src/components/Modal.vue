<template>
  <!--
    TELEPORT : normalement ce composant s'afficherait
    "à l'intérieur" de ProductDetail dans le DOM.
    Teleport le déplace directement dans le <body>.
    
    Parce que si, par exemple, un élément parent a un
    "overflow: hidden" ou un z-index, la modale
    pourrait être cachée ou mal positionnée.
    Avec Teleport, elle flotte au-dessus de tout.
  -->
  <Teleport to="body">

    <!--
      @click.self : le .self signifie "seulement si on clique
      SUR cet élément précis, pas sur ses enfants".
      
      Sans .self : cliquer sur la modale fermerait aussi la modale
      (car le clic "remonte" jusqu'à l'overlay).
      Avec .self : seul un clic sur le fond sombre ferme la modale.
    -->
    <div class="modal-overlay" @click.self="$emit('close')">

      <div class="modal-container">

        <div class="modal-header">
          <!--
            "title" est une prop : une valeur que le parent
            nous envoie. On l'utilise comme une variable normale.
            Exemple d'utilisation depuis le parent :
            <Modal title="Modifier la quantité">
          -->
          <h2>{{ title }}</h2>
          <button class="modal-close" @click="$emit('close')">✕</button>
        </div>

        <!--
          SLOT : c'est un "trou" dans le composant.
          Le parent met ce qu'il veut dedans.
          
          Exemple d'utilisation depuis le parent :
          <Modal title="Modifier la quantité">
            <p>N'importe quel contenu ici</p>
            <input type="number" />
          </Modal>
          
          Tout ce qui est entre les balises <Modal>
          atterrit ici, dans le <slot />.
        -->
        <div class="modal-body">
          <slot />
        </div>

      </div>
    </div>

  </Teleport>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  }
})

// On déclare qu'on peut envoyer l'événement "close" au parent.
// Le parent écoute avec @close="maFonction"
defineEmits(['close'])
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  /*
    "inset: 0" est un raccourci CSS pour écrire :
    top: 0; right: 0; bottom: 0; left: 0;
    Résultat : l'overlay couvre tout l'écran.
  */
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 90%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #6b7280;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  transition: background 0.2s;
}

.modal-close:hover {
  background: #f3f4f6;
}
</style>