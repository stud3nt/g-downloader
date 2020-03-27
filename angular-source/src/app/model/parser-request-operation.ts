import {BaseModel} from "./base/base-model";
import {ParserRequest} from "./parser-request";
import {ParserRequestAction} from "../enum/parser-request-action";

export class ParserRequestOperation extends BaseModel {

	constructor(action: string = null, parserRequest: ParserRequest = null) {
		super();

		if (action)
			this._action = action;

		if (parserRequest)
			this._parserRequest = parserRequest;
	}

	private _action: string = null;

	private _parserRequest: ParserRequest = null;

	get action(): string {
		return this._action;
	}

	set action(value: string) {
		this._action = value;
	}

	get parserRequest(): ParserRequest {
		return this._parserRequest;
	}

	set parserRequest(value: ParserRequest) {
		this._parserRequest = value;
	}
}