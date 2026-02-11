export type Role = {
    id: number
    name: string
}

export type Employee = {
    id: number
    first_name: string
    last_name: string
    role_id?: number
    role_name?: string
}

export type Location = {
    id: number
    name: string
}

export type Category = {
    id: number
    name: string
    status: string
}