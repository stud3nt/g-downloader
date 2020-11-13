export class UpdateNodeValueRequest {

    constructor(obj?: any) {
        Object.assign(this, obj);
    }

    private _identifier:string|null = null;

    private _fieldName: string|null = null;

    private _fieldValue = null;

    get identifier(): string | null {
        return this._identifier;
    }

    set identifier(value: string | null) {
        this._identifier = value;
    }

    get fieldName(): string | null {
        return this._fieldName;
    }

    set fieldName(value: string | null) {
        this._fieldName = value;
    }

    get fieldValue(): any {
        return this._fieldValue;
    }

    set fieldValue(value: any) {
        this._fieldValue = value;
    }
}
