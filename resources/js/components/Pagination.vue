<template>
  <nav class="text-center" role="navigation" aria-label="pagination" v-if="value.last_page > 1" v-cloak>
    <ul class="pagination">
    <li><a @click.prevent="changePage(1)" :disabled="value.current_page <= 1">«</a></li>
    <li><a @click.prevent="changePage(Math.max(value.current_page - 1, 1))" :disabled="value.current_page <= 1">‹</a></li>
    <li v-for="page in pages" :key="page" :class="isCurrentPage(page) ? 'active' : ''">
      <a class="pagination-link" @click.prevent="changePage(page)">
      {{ page }}
      </a>
    </li>
    <li><a @click.prevent="changePage(Math.min(value.current_page + 1, value.last_page))" :disabled="value.current_page >= value.last_page">›</a></li>
    <li><a @click.prevent="changePage(value.last_page)" :disabled="value.current_page >= value.last_page">»</a></li>
    </ul>
  </nav>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      default: {},
    },
    offset: {
      type: Number,
      default: 5,
    },
  },
  methods: {
    changePage(page) {
      this.$emit('input', {
        ...this.value,
        current_page: page,
      });
    },
    isCurrentPage(page) {
      return this.value.current_page === page;
    }
  },
  computed: {
    pages() {
      let pages = [];

      let from = this.value.current_page - Math.floor(this.offset / 2);

      if (from < 1) {
        from = 1;
      }

      let to = from + this.offset - 1;

      if (to > this.value.last_page) {
        to = this.value.last_page;
      }

      while (from <= to) {
        pages.push(from);
        from++;
      }

      return pages;
    }
  },
};
</script>