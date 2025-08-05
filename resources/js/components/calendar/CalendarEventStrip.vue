<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  CheckCircle2, 
  Clock, 
  XCircle, 
  AlertCircle,
  Edit,
  Eye 
} from 'lucide-vue-next';
import { format, parseISO, differenceInDays } from 'date-fns';
import type { CalendarEvent } from '@/types/calendar';

interface Props {
  event: CalendarEvent;
  startCol: number;
  spanCols: number;
  row: number;
  gridStartDate: Date;
  canEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  canEdit: false,
});

const emit = defineEmits<{
  eventClick: [event: CalendarEvent];
  editEvent: [event: CalendarEvent];
  startDrag: [event: MouseEvent];
  startHorizontalResize: [event: MouseEvent, eventData: CalendarEvent, direction: 'left' | 'right'];
  startVerticalResize: [event: MouseEvent, eventData: CalendarEvent, direction: 'up' | 'down'];
  startMove: [event: MouseEvent, eventData: CalendarEvent];
}>();

// Make spanCols available in template
const { spanCols } = props;

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'rejected': return XCircle;
    default: return AlertCircle;
  }
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'default';
    case 'pending': return 'secondary';
    case 'rejected': return 'destructive';
    default: return 'outline';
  }
};

// Determine if the event can be edited (only pending requests by the current user)
const isEditable = computed(() => {
  return props.canEdit && 
         props.event.status === 'pending' && 
         props.event.extendedProps?.isOwnRequest &&
         !props.event.isHoliday;
});

// Style object for positioning the strip using absolute positioning
const stripStyle = computed(() => {
  const leftPercentage = ((props.startCol - 1) / 7) * 100;
  const widthPercentage = (props.spanCols / 7) * 100;
  const topOffset = props.row * 26; // 26px per row for tighter spacing
  
  return {
    position: 'absolute' as const,
    left: `${leftPercentage}%`,
    width: `${widthPercentage}%`,
    top: `${topOffset}px`,
    height: '24px',
    zIndex: 10 + props.row, // Higher rows appear on top
  };
});

// Determine if this is the start of a multi-day event
const isEventStart = computed(() => {
  const eventStartDate = parseISO(props.event.start);
  const gridStartDate = props.gridStartDate;
  return eventStartDate >= gridStartDate;
});

// Determine if this is the end of a multi-day event
const isEventEnd = computed(() => {
  const eventEndDate = parseISO(props.event.end);
  const gridEndDate = new Date(props.gridStartDate);
  gridEndDate.setDate(gridEndDate.getDate() + 6); // End of week
  return eventEndDate <= gridEndDate;
});

// Handle drag start events
const handleLeftResize = (event: MouseEvent) => {
  emit('startHorizontalResize', event, props.event, 'left');
};

const handleRightResize = (event: MouseEvent) => {
  emit('startHorizontalResize', event, props.event, 'right');
};

const handleTopResize = (event: MouseEvent) => {
  emit('startVerticalResize', event, props.event, 'up');
};

const handleBottomResize = (event: MouseEvent) => {
  emit('startVerticalResize', event, props.event, 'down');
};

const handleEventMove = (event: MouseEvent) => {
  // Only allow moving if it's editable and not clicking on resize handles
  if (!isEditable.value) return;
  
  // Check if clicking on a resize handle (they have specific classes)
  const target = event.target as HTMLElement;
  if (target.closest('.resize-handle') || target.closest('[title*="Drag to adjust"]')) {
    return; // Don't start move drag if clicking on resize handles
  }
  
  emit('startMove', event, props.event);
};
</script>

<template>
  <div
    :style="stripStyle"
    :data-event-id="event.id"
    class="group relative rounded transition-all flex items-center overflow-hidden"
    :class="{
      'hover:shadow-lg hover:scale-[1.02] cursor-move': isEditable,
      'cursor-pointer hover:shadow-md': !isEditable,
      'ring-2 ring-blue-400 ring-opacity-50': isEditable,
      'dragging': false, // Will be set by drag service
    }"
    @mousedown="isEditable ? handleEventMove : undefined"
    @click="!isEditable ? emit('eventClick', event) : undefined"
  >
    <!-- Main event strip background -->
    <div 
      class="absolute inset-0 rounded"
      :style="{ 
        backgroundColor: event.backgroundColor,
        opacity: 0.95
      }"
    ></div>

    <!-- Enhanced draggable start indicator for pending requests (left resize) -->
    <div 
      v-if="isEventStart && isEditable"
      class="resize-handle absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l cursor-ew-resize hover:w-2 hover:bg-blue-600 transition-all opacity-60 group-hover:opacity-100 group-hover:shadow-lg z-20"
      :title="'Drag to adjust start date'"
      @mousedown.stop="handleLeftResize"
    >
      <!-- Visual grip indicator -->
      <div class="absolute inset-y-0 left-0 w-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
        <div class="w-0.5 h-3 bg-white rounded-full shadow-sm"></div>
      </div>
    </div>

    <!-- Orange start indicator (visual only when not editable) -->
    <div 
      v-else-if="isEventStart"
      class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500 rounded-l"
    ></div>

    <!-- Enhanced draggable end indicator for pending requests (right resize) -->
    <div 
      v-if="isEventEnd && isEditable"
      class="resize-handle absolute right-0 top-0 bottom-0 w-1 bg-blue-500 rounded-r cursor-ew-resize hover:w-2 hover:bg-blue-600 transition-all opacity-60 group-hover:opacity-100 group-hover:shadow-lg z-20"
      :title="'Drag to adjust end date'"
      @mousedown.stop="handleRightResize"
    >
      <!-- Visual grip indicator -->
      <div class="absolute inset-y-0 right-0 w-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
        <div class="w-0.5 h-3 bg-white rounded-full shadow-sm"></div>
      </div>
    </div>

    <!-- Green end indicator (visual only when not editable) -->
    <div 
      v-else-if="isEventEnd"
      class="absolute right-0 top-0 bottom-0 w-1 bg-green-500 rounded-r"
    ></div>

    <!-- Event content -->
    <div class="relative flex items-center justify-between w-full h-full px-2">
      <div class="flex items-center gap-1 flex-1 min-w-0">
        <!-- Move indicator for editable events -->
        <div 
          v-if="isEditable && isEventStart"
          class="flex items-center justify-center w-3 h-3 opacity-0 group-hover:opacity-70 transition-opacity cursor-move"
          title="Click and drag to move event"
        >
          <svg class="w-2.5 h-2.5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path>
          </svg>
        </div>
        
        <component 
          v-if="isEventStart"
          :is="getStatusIcon(event.status || 'pending')" 
          class="h-3 w-3 shrink-0 text-gray-700" 
        />
        <div 
          v-if="spanCols >= 2 || isEventStart"
          class="font-medium text-xs truncate text-gray-700" 
          :title="event.title"
        >
          {{ spanCols >= 4 ? event.title : (event.title.length > 12 ? event.title.substring(0, 12) + '...' : event.title) }}
        </div>
        <span 
          v-if="spanCols >= 3 && isEventStart && event.days" 
          class="text-xs text-gray-600 shrink-0"
        >
          ({{ event.days }}d)
        </span>
      </div>
      
      <!-- Action buttons - only show if strip is wide enough -->
      <div v-if="spanCols >= 4 && isEventStart" class="flex items-center gap-1 ml-1 shrink-0">
        <Button
          v-if="isEditable"
          variant="ghost"
          size="sm"
          class="h-5 w-5 p-0 hover:bg-white hover:bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity"
          @click.stop="emit('editEvent', event)"
          :title="'Edit leave request'"
        >
          <Edit class="h-2.5 w-2.5 text-gray-700" />
        </Button>
        <Button
          variant="ghost"
          size="sm"
          class="h-5 w-5 p-0 hover:bg-white hover:bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity"
          @click.stop="emit('eventClick', event)"
          :title="'View details'"
        >
          <Eye class="h-2.5 w-2.5 text-gray-700" />
        </Button>
      </div>
    </div>

    <!-- Draggable end indicator for pending requests (right resize) -->
    <div 
      v-if="isEventEnd && isEditable"
      class="absolute right-0 top-0 bottom-0 w-2 bg-blue-600 rounded-r cursor-ew-resize hover:bg-blue-700 transition-colors opacity-0 group-hover:opacity-100"
      :title="'Drag to adjust end date'"
      @mousedown.stop="handleRightResize"
    >
      <div class="absolute inset-y-0 right-0.5 w-0.5 bg-white opacity-80 rounded"></div>
    </div>

    <!-- Tooltip on hover showing dates and details -->
    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50">
      <div class="font-medium">{{ event.title }}</div>
      <div>{{ format(parseISO(event.start), 'MMM dd') }} - {{ format(parseISO(event.end), 'MMM dd, yyyy') }}</div>
      <div>{{ event.days }} day{{ event.days !== 1 ? 's' : '' }} • {{ event.status }}</div>
      <div v-if="event.reason" class="text-xs opacity-75 max-w-xs truncate">{{ event.reason }}</div>
    </div>
  </div>
</template>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

/* Enhanced dragging state styles */
:deep(.dragging) {
  opacity: 0.8;
  transform: scale(1.02);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
  z-index: 1000;
  border: 2px dashed #3b82f6;
}

/* Enhanced resize handles */
.group:hover .resize-handle {
  opacity: 1;
}

/* Move cursor for editable events */
.cursor-move:hover {
  cursor: move;
}

/* Smooth transitions for all interactive elements */
.group .resize-handle,
.group .opacity-0 {
  transition: all 0.2s ease-in-out;
}

/* Enhanced hover effects for better UX */
.group:hover {
  transform: translateY(-1px);
}

.group.cursor-move:hover {
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.3);
}

/* Visual feedback during drag operations */
.group.dragging .resize-handle {
  display: none;
}

.group.dragging {
  cursor: grabbing !important;
}

/* Prevent text selection during drag */
.dragging * {
  user-select: none;
  pointer-events: none;
}
</style>
