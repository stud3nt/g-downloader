import { BaseModel } from "./base/base-model";

export class PaginationSelector extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);

		if (typeof obj.childrens !== 'undefined' && obj.childrens)
			this._childrens = [];

			for (let children of obj.childrens)
				this._childrens.push(
					new PaginationSelector(children)
				);
	}

	// pagination choice label
	private _label: string = '';

	// pagination choice value
	private _value: string = '';

	// is selector active
	private _isActive: boolean = false;

	// pagination: current page
	private _childrens: PaginationSelector[] = [];

	public getActiveChildren(): (PaginationSelector | null) {
		if (this._childrens) {
			for (let child of this._childrens)
				if (child.isActive === true)
					return child;

			return this._childrens[0];
		}

		return null;
	}

	public setActiveChildrenByValue(childrenValue: string): void {
		if (this._childrens && childrenValue)
			for (let childKey in this._childrens)
				this._childrens[childKey].isActive = (this._childrens[childKey].value === childrenValue);
	}

	public deactivateChildrens(): void {
		if (!this._childrens)
			for (let childKey in this._childrens)
				this._childrens[childKey].isActive = false;
	}

	get label(): string {
		return this._label;
	}

	set label(value: string) {
		this._label = value;
	}

	get value(): string {
		return this._value;
	}

	set value(value: string) {
		this._value = value;
	}

	get isActive(): boolean {
		return this._isActive;
	}

	set isActive(value: boolean) {
		this._isActive = value;
	}

	get childrens(): PaginationSelector[] {
		return this._childrens;
	}

	set childrens(value: PaginationSelector[]) {
		this._childrens = value;
	}
}