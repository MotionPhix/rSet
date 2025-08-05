export interface CalendarEvent {
  id: number | string;
  title: string;
  start: string;
  end: string;
  type: string;
  status?: string;
  reason?: string;
  user_name?: string;
  user_id?: number;
  days?: number;
  color: string;
  backgroundColor: string;
  borderColor: string;
  allDay: boolean;
  isHoliday?: boolean;
  extendedProps?: {
    status?: string;
    type?: string;
    reason?: string;
    appliedAt?: string;
    isOwnRequest?: boolean;
    description?: string;
    isRecurring?: boolean;
    recurrenceType?: string;
    createdBy?: string;
  };
}
