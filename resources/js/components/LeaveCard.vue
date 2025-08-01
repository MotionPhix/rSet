<template>
  <section class="w-full max-w-md md:max-w-3xl mx-auto bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 p-4 md:p-6 space-y-4">
    <div class="flex justify-between items-start flex-wrap gap-2">
      <h2 class="text-base md:text-lg font-semibold text-indigo-600 dark:text-indigo-400">{{ leave.type_display }}</h2>
      <span :class="badgeClasses(leave.status)">
        {{ leave.status_display }}
      </span>
    </div>

    <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 italic">“{{ leave.reason }}”</p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm md:text-base text-gray-700 dark:text-gray-300">
      <div>
        <span class="font-medium text-gray-500 dark:text-gray-400">Start:</span>
        <div class="text-gray-900 dark:text-gray-100">{{ leave.start_date }}</div>
      </div>
      <div>
        <span class="font-medium text-gray-500 dark:text-gray-400">End:</span>
        <div class="text-gray-900 dark:text-gray-100">{{ leave.end_date }}</div>
      </div>
      <div>
        <span class="font-medium text-gray-500 dark:text-gray-400">Days:</span>
        <div class="text-gray-900 dark:text-gray-100">{{ leave.days }} day{{ leave.days > 1 ? 's' : '' }}</div>
      </div>
      <div>
        <span class="font-medium text-gray-500 dark:text-gray-400">Submitted:</span>
        <div class="text-gray-900 dark:text-gray-100">{{ leave.submitted_at }}</div>
      </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pt-4 border-t border-gray-200 dark:border-gray-700 gap-2">
      <div class="text-sm text-gray-500 dark:text-gray-400">
        Approved by <span class="font-semibold text-gray-800 dark:text-gray-200">{{ leave.approver_name }}</span>
      </div>
      <div class="flex space-x-2">
        <button class="px-3 py-1 text-xs border rounded"
                :class="buttonClass(leave.can_edit)"
                :disabled="!leave.can_edit">Edit</button>
        <button class="px-3 py-1 text-xs border rounded"
                :class="buttonClass(leave.can_cancel)"
                :disabled="!leave.can_cancel">Cancel</button>
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  leave: Object
})

const badgeClasses = (status) => {
  const base = 'px-3 py-1 text-sm font-medium rounded-full border';
  switch (status) {
    case 'approved': return `${base} bg-green-100 text-green-700 border-green-300`;
    case 'pending': return `${base} bg-yellow-100 text-yellow-700 border-yellow-300`;
    case 'rejected': return `${base} bg-red-100 text-red-700 border-red-300`;
    default:         return `${base} bg-gray-100 text-gray-700 border-gray-300`;
  }
}

const buttonClass = (active) => {
  return active
    ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-100 border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer'
    : 'bg-gray-100 text-gray-400 border-gray-300 dark:bg-gray-700 dark:text-gray-500 cursor-not-allowed';
}
</script>
