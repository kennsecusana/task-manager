export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export interface Task {
  id: number
  statement: string
  is_completed: boolean
  task_date: string
  sort_order: number     
  created_at: string
  updated_at: string
}

export interface TaskCreatePayload {
  statement: string
  task_date: string     
}

export interface TaskUpdatePayload {
  statement?: string
  is_completed?: boolean
  task_date?: string
  sort_order?: number   
}

export interface TaskReorderPayload {
  tasks: { id: number; sort_order: number }[] 
}

export interface User {
  id: number
  name: string
  email: string
  image_url: string | null
  created_at: string
  updated_at: string
}

export interface LoginPayload {
  email: string
  password: string
}