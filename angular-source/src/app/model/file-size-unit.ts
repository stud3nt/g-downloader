export class FileSizeUnit {

	private _name: string = '';

	private _symbol: string = '';

	private _multiplier: number = 0;

    get name(): string {
        return this._name;
    }

    set name(value: string) {
        this._name = value;
    }

    get symbol(): string {
        return this._symbol;
    }

    set symbol(value: string) {
        this._symbol = value;
    }

    get multiplier(): number {
        return this._multiplier;
    }

    set multiplier(value: number) {
        this._multiplier = value;
    }
}
