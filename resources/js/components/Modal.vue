<template>
  <div>
    <transition name="fade">
      <div v-if="value" :class="{'modal-backdrop': true, 'fade': true, in: true}"></div>
    </transition>
    <transition name="fade-down">
      <div v-if="value" class="modal fade in" tabindex="-1" role="dialog" @click.stop.self="close" style="display: block">
        <div :class="['modal-dialog', `modal-${size}`]" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" @click.prevent="close">
                <span>&times;</span>
              </button>
              <slot name="header">
                <h4 class="modal-title" v-if="title">{{title}}</h4>
              </slot>
            </div>
            <div class="modal-body">
              <slot></slot>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click.prevent="close">{{cancelText}}</button>
              <slot name="footer"></slot>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Boolean,
      default: false
    },
    cancelText: {
      type: String,
      default: 'Cancel',
    },
    size: {
      type: String,
      default: 'lg',
    },
    title: '',
  },
  watch: {
    value(newValue, oldValue) {
      document.body.classList[newValue ? 'add' : 'remove']('modal-open');
    },
  },
  methods: {
    close() {
      this.$emit('close');
    },
  }
}
</script>

<style>
.fade-down-enter,
.fade-down-leave-to {
  opacity: 0 !important;
}
.fade-down-enter-to,
.fade-down-leave {
  opacity: 1 !important;
}
.fade-down-enter-active,
.fade-down-leave-active {
  transition: opacity 200ms ease-out;
}
.fade-down-enter .modal-content,
.fade-down-leave-to  .modal-content{
  transform: scale(.9);
}
.fade-down-enter-to .modal-content,
.fade-down-leave  .modal-content{
  transform: scale(1);
}
.fade-down-enter-active .modal-content,
.fade-down-leave-active  .modal-content{
  transition: transform 200ms ease-out;
}
</style>