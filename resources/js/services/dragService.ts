import { addDays, subDays, parseISO, format, differenceInDays } from 'date-fns';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export interface DragState {
  isDragging: boolean;
  dragType: 'horizontal' | 'vertical' | 'move' | null;
  direction: 'left' | 'right' | 'up' | 'down' | null;
  originalEvent: any;
  originalStartDate: Date;
  originalEndDate: Date;
  currentStartDate: Date;
  currentEndDate: Date;
  startMouseX: number;
  startMouseY: number;
  eventDuration: number; // Store original duration for move operations
}

export class DragService {
  private dragState: DragState = {
    isDragging: false,
    dragType: null,
    direction: null,
    originalEvent: null,
    originalStartDate: new Date(),
    originalEndDate: new Date(),
    currentStartDate: new Date(),
    currentEndDate: new Date(),
    startMouseX: 0,
    startMouseY: 0,
    eventDuration: 1,
  };

  private cellWidth = 0;
  private cellHeight = 0;
  private calendarElement: HTMLElement | null = null;
  private snapIndicator: HTMLElement | null = null;
  private ghostElement: HTMLElement | null = null;
  private originalEventElement: HTMLElement | null = null;

  startHorizontalDrag(
    mouseEvent: MouseEvent, 
    event: any, 
    direction: 'left' | 'right',
    calendarElement: HTMLElement
  ) {
    this.setupDrag(mouseEvent, event, 'horizontal', direction, calendarElement);
  }

  startVerticalDrag(
    mouseEvent: MouseEvent, 
    event: any, 
    direction: 'up' | 'down',
    calendarElement: HTMLElement
  ) {
    this.setupDrag(mouseEvent, event, 'vertical', direction, calendarElement);
  }

  startMoveDrag(
    mouseEvent: MouseEvent,
    event: any,
    calendarElement: HTMLElement
  ) {
    this.setupDrag(mouseEvent, event, 'move', null, calendarElement);
  }

  private setupDrag(
    mouseEvent: MouseEvent,
    event: any,
    dragType: 'horizontal' | 'vertical' | 'move',
    direction: 'left' | 'right' | 'up' | 'down' | null,
    calendarElement: HTMLElement
  ) {
    // Prevent default to avoid text selection
    mouseEvent.preventDefault();
    
    console.log('🎯 Starting fluid drag operation:', {
      type: dragType,
      direction,
      eventId: event.id,
      originalDates: {
        start: format(parseISO(event.start), 'yyyy-MM-dd'),
        end: format(parseISO(event.end), 'yyyy-MM-dd'),
      }
    });
    
    // Store calendar element reference
    this.calendarElement = calendarElement;
    
    // Calculate cell dimensions more precisely
    this.calculateCellDimensions();

    // Find and store the original event element
    this.originalEventElement = calendarElement.querySelector(`[data-event-id="${event.id}"]`);
    
    // Create ghost element for fluid dragging
    this.createGhostElement(event);
    
    // Create enhanced snap indicator
    this.createSnapIndicator();

    // Setup drag state
    this.dragState = {
      isDragging: true,
      dragType,
      direction,
      originalEvent: event,
      originalStartDate: parseISO(event.start),
      originalEndDate: parseISO(event.end),
      currentStartDate: parseISO(event.start),
      currentEndDate: parseISO(event.end),
      startMouseX: mouseEvent.clientX,
      startMouseY: mouseEvent.clientY,
      eventDuration: differenceInDays(parseISO(event.end), parseISO(event.start)) + 1,
    };

    // Hide original element and show ghost
    if (this.originalEventElement) {
      this.originalEventElement.style.opacity = '0.3';
      this.originalEventElement.style.pointerEvents = 'none';
    }

    // Add event listeners with proper binding
    document.addEventListener('mousemove', this.handleMouseMove, { passive: false });
    document.addEventListener('mouseup', this.handleMouseUp, { once: true });
    
    // Set cursor and prevent selection
    if (dragType === 'move') {
      document.body.style.cursor = 'move';
    } else {
      document.body.style.cursor = dragType === 'horizontal' ? 'ew-resize' : 'ns-resize';
    }
    document.body.style.userSelect = 'none';
    document.body.classList.add('dragging');
  }

  /**
   * Calculate precise cell dimensions for smooth dragging
   */
  private calculateCellDimensions() {
    if (!this.calendarElement) return;
    
    const calendarRect = this.calendarElement.getBoundingClientRect();
    
    // Find a day cell to get precise dimensions
    const dayCell = this.calendarElement.querySelector('[data-date]');
    if (dayCell) {
      const cellRect = dayCell.getBoundingClientRect();
      this.cellWidth = cellRect.width;
      this.cellHeight = cellRect.height;
    } else {
      // Fallback calculation
      this.cellWidth = calendarRect.width / 7; // 7 days in a week
      this.cellHeight = 120; // Approximate cell height
    }
    
    console.log('📐 Cell dimensions:', { width: this.cellWidth, height: this.cellHeight });
  }

  /**
   * Create a ghost element for fluid dragging
   */
  private createGhostElement(event: any) {
    if (this.ghostElement) {
      this.ghostElement.remove();
    }

    this.ghostElement = document.createElement('div');
    this.ghostElement.className = 'drag-ghost-element';
    
    // Enhanced styling for move operations
    const isMove = this.dragState.dragType === 'move';
    const durationText = isMove ? ` (${this.dragState.eventDuration}d)` : '';
    
    this.ghostElement.style.cssText = `
      position: fixed;
      background: ${event.backgroundColor};
      border: 2px solid ${event.borderColor || event.color};
      border-radius: 6px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      color: #374151;
      pointer-events: none;
      z-index: 9999;
      opacity: 0.95;
      transform: translateY(-50%);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
      transition: none;
      white-space: nowrap;
      min-width: ${isMove ? '120px' : 'auto'};
      text-align: center;
      ${isMove ? 'border-style: dashed; animation: pulse 1.5s ease-in-out infinite;' : ''}
    `;
    
    this.ghostElement.innerHTML = `
      <div>${event.title}${durationText}</div>
      ${isMove ? '<div style="font-size: 10px; opacity: 0.8; margin-top: 2px;">📅 Moving...</div>' : ''}
    `;
    
    document.body.appendChild(this.ghostElement);
    
    // Add CSS animation for pulse effect
    if (isMove && !document.getElementById('drag-pulse-styles')) {
      const style = document.createElement('style');
      style.id = 'drag-pulse-styles';
      style.textContent = `
        @keyframes pulse {
          0%, 100% { transform: translateY(-50%) scale(1); }
          50% { transform: translateY(-50%) scale(1.05); }
        }
      `;
      document.head.appendChild(style);
    }
  }

  private handleMouseMove = (event: MouseEvent) => {
    if (!this.dragState.isDragging) return;
    
    event.preventDefault();

    const deltaX = event.clientX - this.dragState.startMouseX;
    const deltaY = event.clientY - this.dragState.startMouseY;
    
    // Update ghost element position
    this.updateGhostPosition(event.clientX, event.clientY);

    if (this.dragState.dragType === 'horizontal') {
      this.handleHorizontalDrag(deltaX);
    } else if (this.dragState.dragType === 'vertical') {
      this.handleVerticalDrag(deltaY);
    } else if (this.dragState.dragType === 'move') {
      this.handleMoveDrag(event.clientX, event.clientY);
    }
    
    // Update visual feedback
    this.updateSnapIndicator();
  };

  /**
   * Update ghost element position to follow mouse
   */
  private updateGhostPosition(mouseX: number, mouseY: number) {
    if (!this.ghostElement) return;
    
    this.ghostElement.style.left = `${mouseX + 10}px`;
    this.ghostElement.style.top = `${mouseY}px`;
  }

  private handleHorizontalDrag(deltaX: number) {
    // Calculate mouse position relative to calendar for precise cell detection
    if (!this.calendarElement) return;
    
    const currentMouseX = this.dragState.startMouseX + deltaX;
    const currentMouseY = this.dragState.startMouseY;
    
    // Find the target cell based on mouse position (supports cross-row dragging)
    const targetCell = this.findCellAtPosition(currentMouseX, currentMouseY);
    
    if (!targetCell) {
      // Fallback to original grid-based calculation
      const daysDelta = Math.round(deltaX / this.cellWidth);
      
      if (this.dragState.direction === 'left') {
        const newStartDate = addDays(this.dragState.originalStartDate, daysDelta);
        if (newStartDate < this.dragState.currentEndDate) {
          this.dragState.currentStartDate = newStartDate;
        }
      } else if (this.dragState.direction === 'right') {
        const newEndDate = addDays(this.dragState.originalEndDate, daysDelta);
        if (newEndDate > this.dragState.currentStartDate) {
          this.dragState.currentEndDate = newEndDate;
        }
      }
      return;
    }
    
    const targetDateStr = targetCell.dataset.date;
    if (!targetDateStr) return;
    
    const targetDate = parseISO(targetDateStr);
    
    console.log('🎯 Cross-row drag to:', {
      targetDate: format(targetDate, 'yyyy-MM-dd'),
      direction: this.dragState.direction,
      cellPosition: targetCell.getBoundingClientRect(),
    });
    
    if (this.dragState.direction === 'left') {
      // Dragging start date (left handle) - can drag to any valid date before end
      if (targetDate < this.dragState.currentEndDate) {
        this.dragState.currentStartDate = targetDate;
      }
    } else if (this.dragState.direction === 'right') {
      // Dragging end date (right handle) - can drag to any valid date after start
      if (targetDate > this.dragState.currentStartDate) {
        this.dragState.currentEndDate = targetDate;
      }
    }
  }

  private handleVerticalDrag(deltaY: number) {
    // Enhanced vertical dragging with cross-row support
    if (!this.calendarElement) return;
    
    const currentMouseX = this.dragState.startMouseX;
    const currentMouseY = this.dragState.startMouseY + deltaY;
    
    // Find the target cell based on mouse position
    const targetCell = this.findCellAtPosition(currentMouseX, currentMouseY);
    
    if (!targetCell) {
      // Fallback to week-based calculation
      const weeksDelta = Math.round(deltaY / this.cellHeight);
      const daysDelta = weeksDelta * 7;
      
      if (this.dragState.direction === 'up') {
        const newStartDate = subDays(this.dragState.originalStartDate, daysDelta);
        if (newStartDate < this.dragState.currentEndDate) {
          this.dragState.currentStartDate = newStartDate;
        }
      } else if (this.dragState.direction === 'down') {
        const newEndDate = addDays(this.dragState.originalEndDate, daysDelta);
        if (newEndDate > this.dragState.currentStartDate) {
          this.dragState.currentEndDate = newEndDate;
        }
      }
      return;
    }
    
    const targetDateStr = targetCell.dataset.date;
    if (!targetDateStr) return;
    
    const targetDate = parseISO(targetDateStr);
    
    console.log('🎯 Vertical cross-row drag to:', {
      targetDate: format(targetDate, 'yyyy-MM-dd'),
      direction: this.dragState.direction,
    });
    
    if (this.dragState.direction === 'up') {
      // Dragging start date up (earlier weeks)
      if (targetDate < this.dragState.currentEndDate) {
        this.dragState.currentStartDate = targetDate;
      }
    } else if (this.dragState.direction === 'down') {
      // Dragging end date down (later weeks)
      if (targetDate > this.dragState.currentStartDate) {
        this.dragState.currentEndDate = targetDate;
      }
    }
  }

  private handleMoveDrag(mouseX: number, mouseY: number) {
    // Handle moving entire event to a new date (FullCalendar style)
    if (!this.calendarElement) return;
    
    // Find the target cell based on mouse position
    const targetCell = this.findCellAtPosition(mouseX, mouseY);
    
    if (!targetCell) return;
    
    const targetDateStr = targetCell.dataset.date;
    if (!targetDateStr) return;
    
    const targetDate = parseISO(targetDateStr);
    
    // Check if the target date is in the future (forward-only movement)
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (targetDate < today) {
      console.log('❌ Cannot move leave to past date:', format(targetDate, 'yyyy-MM-dd'));
      return;
    }
    
    // Also check if target date is not before original start date (no moving backwards)
    if (targetDate < this.dragState.originalStartDate) {
      console.log('❌ Cannot move leave to earlier date than original:', {
        targetDate: format(targetDate, 'yyyy-MM-dd'),
        originalStart: format(this.dragState.originalStartDate, 'yyyy-MM-dd')
      });
      return;
    }
    
    // Calculate new end date based on original duration
    const newEndDate = addDays(targetDate, this.dragState.eventDuration - 1);
    
    console.log('🎯 Moving event to new dates:', {
      targetDate: format(targetDate, 'yyyy-MM-dd'),
      newEndDate: format(newEndDate, 'yyyy-MM-dd'),
      duration: this.dragState.eventDuration,
    });
    
    this.dragState.currentStartDate = targetDate;
    this.dragState.currentEndDate = newEndDate;
  }

  private updatePreview() {
    // Visual feedback - add a class to show dragging state
    const eventElement = document.querySelector(`[data-event-id="${this.dragState.originalEvent.id}"]`);
    if (eventElement) {
      eventElement.classList.add('dragging');
    }
    
    const newDays = differenceInDays(this.dragState.currentEndDate, this.dragState.currentStartDate) + 1;
    const startFormatted = format(this.dragState.currentStartDate, 'MMM dd');
    const endFormatted = format(this.dragState.currentEndDate, 'MMM dd');
    
    // Update snap indicator
    this.updateSnapIndicator();
    
    // Show a temporary toast with the new dates
    console.log(`Dragging: ${startFormatted} - ${endFormatted} (${newDays} days)`);
    
    // You could add a temporary overlay or tooltip here showing the new dates
  }

  /**
   * Find calendar cell at specific mouse coordinates (supports cross-row detection)
   */
  private findCellAtPosition(mouseX: number, mouseY: number): HTMLElement | null {
    if (!this.calendarElement) return null;
    
    // Use elementFromPoint to find the exact element under mouse cursor
    const elementAtPoint = document.elementFromPoint(mouseX, mouseY);
    
    if (!elementAtPoint) return null;
    
    // Find the closest calendar day cell
    const dayCell = elementAtPoint.closest('[data-date]') as HTMLElement;
    
    if (dayCell && this.calendarElement.contains(dayCell)) {
      console.log('📍 Found cell at position:', {
        date: dayCell.dataset.date,
        mouseX,
        mouseY,
        cellRect: dayCell.getBoundingClientRect(),
      });
      return dayCell;
    }
    
    // Fallback: try to find cell by calculating grid position
    const calendarRect = this.calendarElement.getBoundingClientRect();
    const relativeX = mouseX - calendarRect.left;
    const relativeY = mouseY - calendarRect.top;
    
    const col = Math.floor(relativeX / this.cellWidth);
    const row = Math.floor(relativeY / this.cellHeight);
    
    // Find cell by grid coordinates
    const allCells = this.calendarElement.querySelectorAll('[data-date]');
    const targetIndex = row * 7 + col;
    
    if (targetIndex >= 0 && targetIndex < allCells.length) {
      const cell = allCells[targetIndex] as HTMLElement;
      console.log('📍 Found cell by grid calculation:', {
        date: cell.dataset.date,
        col,
        row,
        targetIndex,
      });
      return cell;
    }
    
    return null;
  }

  private createSnapIndicator() {
    if (!this.calendarElement) return;

    // Create visual indicator for snapping with enhanced styling
    this.snapIndicator = document.createElement('div');
    this.snapIndicator.className = 'drag-snap-indicator';
    this.snapIndicator.style.cssText = `
      position: absolute;
      background: rgba(59, 130, 246, 0.2);
      border: 2px dashed #3b82f6;
      border-radius: 8px;
      pointer-events: none;
      z-index: 1001;
      display: none;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    `;
    
    // Add duration display
    const durationDisplay = document.createElement('div');
    durationDisplay.className = 'drag-duration-display';
    durationDisplay.style.cssText = `
      position: absolute;
      top: -32px;
      left: 50%;
      transform: translateX(-50%);
      background: #1f2937;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      opacity: 0.9;
    `;
    this.snapIndicator.appendChild(durationDisplay);
    
    document.body.appendChild(this.snapIndicator);
  }

  private updateSnapIndicator() {
    if (!this.snapIndicator || !this.calendarElement) return;

    // Calculate duration
    const duration = differenceInDays(this.dragState.currentEndDate, this.dragState.currentStartDate) + 1;
    
    // Update duration display
    const durationDisplay = this.snapIndicator.querySelector('.drag-duration-display');
    if (durationDisplay) {
      durationDisplay.textContent = `${duration} day${duration !== 1 ? 's' : ''}`;
    }

    // Calculate which cells the event would span
    const calendarRect = this.calendarElement.getBoundingClientRect();
    
    // Find the start and end dates on the calendar
    const startCell = this.findCellForDate(this.dragState.currentStartDate);
    const endCell = this.findCellForDate(this.dragState.currentEndDate);
    
    if (startCell && endCell) {
      const startRect = startCell.getBoundingClientRect();
      const endRect = endCell.getBoundingClientRect();
      
      // Calculate the combined area
      const left = Math.min(startRect.left, endRect.left);
      const right = Math.max(startRect.right, endRect.right);
      const top = Math.min(startRect.top, endRect.top);
      const bottom = Math.max(startRect.bottom, endRect.bottom);
      
      // Position the indicator with smooth animation
      this.snapIndicator.style.left = `${left}px`;
      this.snapIndicator.style.top = `${top + 35}px`; // Offset for day header
      this.snapIndicator.style.width = `${right - left}px`;
      this.snapIndicator.style.height = `35px`; // Height for event strip
      this.snapIndicator.style.display = 'block';
      this.snapIndicator.style.opacity = '1';
      
      console.log('📍 Snap indicator updated:', {
        duration: `${duration} days`,
        dates: {
          start: format(this.dragState.currentStartDate, 'yyyy-MM-dd'),
          end: format(this.dragState.currentEndDate, 'yyyy-MM-dd'),
        },
        position: { left, top: top + 35, width: right - left }
      });
    }
  }

  private findCellForDate(date: Date): HTMLElement | null {
    if (!this.calendarElement) return null;
    
    // Look for calendar day cells
    const dayCells = this.calendarElement.querySelectorAll('[data-date]');
    
    for (const cell of dayCells) {
      const cellDate = (cell as HTMLElement).dataset.date;
      if (cellDate && format(date, 'yyyy-MM-dd') === cellDate) {
        return cell as HTMLElement;
      }
    }
    
    return null;
  }

  private handleMouseUp = () => {
    if (!this.dragState.isDragging) return;

    // Check if dates actually changed
    const startChanged = this.dragState.currentStartDate.getTime() !== this.dragState.originalStartDate.getTime();
    const endChanged = this.dragState.currentEndDate.getTime() !== this.dragState.originalEndDate.getTime();

    if (startChanged || endChanged) {
      this.submitChanges();
    }

    this.cleanup();
  };

  private async submitChanges() {
    const eventId = this.dragState.originalEvent.id;
    const newStartDate = format(this.dragState.currentStartDate, 'yyyy-MM-dd');
    const newEndDate = format(this.dragState.currentEndDate, 'yyyy-MM-dd');
    const durationDays = differenceInDays(this.dragState.currentEndDate, this.dragState.currentStartDate) + 1;

    console.log('=== DRAG SUBMIT DEBUG ===');
    console.log('Event ID:', eventId);
    console.log('Original event:', this.dragState.originalEvent);
    console.log('New Start Date:', newStartDate);
    console.log('New End Date:', newEndDate);
    console.log('Duration:', durationDays, 'days');

    // Check for holiday conflicts before submitting
    try {
      const conflictCheck = await this.checkHolidayConflicts(newStartDate, newEndDate);
      if (conflictCheck.hasConflicts) {
        console.warn('🚫 Holiday conflict detected:', conflictCheck.conflicts);
        toast.error(`Cannot schedule leave during holidays: ${conflictCheck.conflicts.join(', ')}`);
        this.resetToOriginalPosition();
        return;
      }
    } catch (error) {
      console.warn('Could not check holiday conflicts:', error);
      // Continue with submission anyway
    }
    
    // For drag operations, we only need to send the dates with drag flag
    const updateData = {
      start_date: newStartDate,
      end_date: newEndDate,
      isDragOperation: true, // This tells backend it's a drag operation
    };
    
    console.log('Submitting data:', updateData);
    console.log('Route:', route('leave-requests.update', eventId));

    try {
      await new Promise<void>((resolve, reject) => {
        router.put(
          route('leave-requests.update', eventId),
          updateData,
          {
            preserveScroll: true,
            preserveState: true,
            only: ['calendarData', 'teamData'], // Only reload calendar data, not the whole page
            onSuccess: (page) => {
              console.log('✅ Drag update successful!');
              console.log('Response page props:', page.props);
              toast.success(`Leave request updated successfully! (${durationDays} days)`);
              resolve();
            },
            onError: (errors) => {
              console.error('❌ Drag update failed:');
              console.error('Validation errors:', errors);
              console.error('Failed data was:', updateData);
              
              // Reset to original position on error
              this.resetToOriginalPosition();
              
              // Handle validation errors
              Object.entries(errors).forEach(([field, messages]) => {
                console.error(`  ${field}:`, messages);
                if (Array.isArray(messages)) {
                  messages.forEach((message) => toast.error(message));
                } else {
                  toast.error(messages as string);
                }
              });
              
              reject(new Error('Validation failed'));
            },
            onFinish: () => {
              console.log('🏁 Drag request finished');
            }
          }
        );
      });
    } catch (error) {
      console.error('❌ Failed to update leave request:', error);
      toast.error('Failed to update leave request. Please try again.');
    }
  }

  /**
   * Check for holiday conflicts in the given date range
   */
  private async checkHolidayConflicts(startDate: string, endDate: string): Promise<{
    hasConflicts: boolean;
    conflicts: string[];
  }> {
    try {
      const year = new Date(startDate).getFullYear();
      const response = await fetch(route('admin.holidays.calendar', { year }));
      
      if (!response.ok) {
        throw new Error('Failed to fetch holidays');
      }
      
      const holidays = await response.json();
      const conflicts: string[] = [];
      
      // Check each day in the date range
      const start = new Date(startDate);
      const end = new Date(endDate);
      
      for (let date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
        const dateStr = format(date, 'yyyy-MM-dd');
        
        for (const holiday of holidays) {
          if (holiday.start === dateStr) {
            conflicts.push(holiday.title);
          }
        }
      }
      
      return {
        hasConflicts: conflicts.length > 0,
        conflicts,
      };
    } catch (error) {
      console.error('Error checking holiday conflicts:', error);
      throw error;
    }
  }

  /**
   * Reset the event to its original position
   */
  private resetToOriginalPosition() {
    this.dragState.currentStartDate = this.dragState.originalStartDate;
    this.dragState.currentEndDate = this.dragState.originalEndDate;
    this.updateSnapIndicator();
    
    // Add visual feedback for reset
    if (this.snapIndicator) {
      this.snapIndicator.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
      this.snapIndicator.style.borderColor = '#ef4444';
      
      setTimeout(() => {
        if (this.snapIndicator) {
          this.snapIndicator.style.backgroundColor = 'rgba(59, 130, 246, 0.2)';
          this.snapIndicator.style.borderColor = '#3b82f6';
        }
      }, 1000);
    }
  }

  private cleanup() {
    // Restore original event element
    if (this.originalEventElement) {
      this.originalEventElement.style.opacity = '1';
      this.originalEventElement.style.pointerEvents = 'auto';
      this.originalEventElement = null;
    }
    
    // Remove ghost element
    if (this.ghostElement) {
      document.body.removeChild(this.ghostElement);
      this.ghostElement = null;
    }
    
    // Remove snap indicator
    if (this.snapIndicator) {
      document.body.removeChild(this.snapIndicator);
      this.snapIndicator = null;
    }
    
    // Reset drag state
    this.dragState.isDragging = false;
    
    // Remove event listeners
    document.removeEventListener('mousemove', this.handleMouseMove);
    document.removeEventListener('mouseup', this.handleMouseUp);
    
    // Reset body styles
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    document.body.classList.remove('dragging');
    
    // Clear references
    this.calendarElement = null;
    
    console.log('🧹 Drag cleanup completed');
  }

  // Public method to check if currently dragging
  isDragging(): boolean {
    return this.dragState.isDragging;
  }

  // Public method to get current drag state
  getDragState(): Readonly<DragState> {
    return { ...this.dragState };
  }
}

// Singleton instance
export const dragService = new DragService();
