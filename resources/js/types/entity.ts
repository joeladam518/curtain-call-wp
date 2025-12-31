export type EntityString = {
    raw: string;
    rendered: string;
}


interface Entity {
    id?: number;
    title?: EntityString;
    meta?: Record<string, any>;
}

export default Entity;
